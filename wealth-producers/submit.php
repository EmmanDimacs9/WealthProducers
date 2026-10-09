<?php
require __DIR__ . '/config.php';
ensure_applicant_schema();

function back_with(array $errors, array $old): never {
    $_SESSION['errors'] = $errors;
    $_SESSION['old'] = $old;
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// CSRF + honeypot
if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    http_response_code(403);
    exit('Invalid session. Please go back and try again.');
}
if (!empty($_POST['website'])) {
    header('Location: success.php');
    exit;
}

$in = [
    'full_name'              => trim($_POST['full_name'] ?? ''),
    'date_of_birth'          => trim($_POST['date_of_birth'] ?? ''),
    'address'                => trim($_POST['address'] ?? ''),
    'email'                  => trim($_POST['email'] ?? ''),
    'contact_number'         => trim($_POST['contact_number'] ?? ''),
    'position'               => $_POST['position'] ?? '',
    'emergency_name'         => trim($_POST['emergency_name'] ?? ''),
    'emergency_number'       => trim($_POST['emergency_number'] ?? ''),
    'emergency_relationship' => $_POST['emergency_relationship'] ?? '',
    'skills'                 => array_values(array_intersect(SKILLS, (array)($_POST['skills'] ?? []))),
    'other_niche'            => trim($_POST['other_niche'] ?? ''),
    'bank_tf'                => !empty($_POST['bank_tf']),
    'assess_tf'              => !empty($_POST['assess_tf']),
    'contract_tf'            => !empty($_POST['contract_tf']),
];

$errors = [];
if ($in['full_name'] === '') $errors[] = 'Full name is required.';
$d = DateTime::createFromFormat('Y-m-d', $in['date_of_birth']);
if (!$d || $d->format('Y-m-d') !== $in['date_of_birth'] || $d > new DateTime()) $errors[] = 'Please enter a valid date of birth.';
if ($in['address'] === '') $errors[] = 'Address is required.';
if (!filter_var($in['email'], FILTER_VALIDATE_EMAIL) || !preg_match('/^[^@\s]+@gmail\.com$/i', $in['email'])) $errors[] = 'Email must be complete and end with @gmail.com.';
if (!preg_match('/^09[0-9]{9}$/', $in['contact_number'])) $errors[] = 'Contact number must be 11 digits (e.g. 09XXXXXXXXX).';
if (!in_array($in['position'], POSITIONS, true)) $errors[] = 'Please choose a position.';
if ($in['emergency_name'] === '') $errors[] = 'Emergency contact name is required.';
if (!preg_match('/^09[0-9]{9}$/', $in['emergency_number'])) $errors[] = 'Emergency contact number must be 11 digits (e.g. 09XXXXXXXXX).';
if (!in_array($in['emergency_relationship'], RELATIONSHIPS, true)) $errors[] = 'Please choose a relationship.';

/** Validate one uploaded file and store it. Returns stored filename or null. */
function store_file(array $f, array $allowed, array &$errors, string $label): ?string {
    if ($f['error'] !== UPLOAD_ERR_OK) { $errors[] = "$label failed to upload."; return null; }
    if ($f['size'] > MAX_UPLOAD_BYTES) { $errors[] = "$label is larger than 5 MB."; return null; }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!isset($allowed[$mime])) { $errors[] = "$label has an invalid file type."; return null; }

    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($f['tmp_name'], UPLOAD_DIR . $name)) { $errors[] = "$label could not be saved."; return null; }
    return $name;
}

/** Single optional upload. */
function save_upload(string $field, array $allowed, array &$errors, string $label): ?string {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
    return store_file($_FILES[$field], $allowed, $errors, $label);
}

/** Multiple optional uploads (max $max). Returns list of stored filenames. */
function save_uploads(string $field, array $allowed, array &$errors, string $label, int $max): array {
    if (empty($_FILES[$field]) || !is_array($_FILES[$field]['name'])) return [];
    $files = [];
    foreach ($_FILES[$field]['name'] as $i => $n) {
        if ($_FILES[$field]['error'][$i] === UPLOAD_ERR_NO_FILE) continue;
        $files[] = [
            'name'     => $n,
            'tmp_name' => $_FILES[$field]['tmp_name'][$i],
            'error'    => $_FILES[$field]['error'][$i],
            'size'     => $_FILES[$field]['size'][$i],
        ];
    }
    if (count($files) > $max) { $errors[] = "$label: please upload no more than $max files."; return []; }
    $saved = [];
    foreach ($files as $k => $f) {
        $s = store_file($f, $allowed, $errors, $label . ' #' . ($k + 1));
        if ($s) $saved[] = $s;
    }
    return $saved;
}

$images = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
$pdf    = ['application/pdf' => 'pdf'];

$bank     = save_upload('bank_info', $images, $errors, 'Bank info');
$assess   = save_uploads('assessment', $images, $errors, 'Assessments', 3);
$contract = save_upload('contract', $pdf, $errors, 'Signed contract');

// Each document: complete file(s) OR "To follow" ticked
if (!$bank && !$in['bank_tf'])               $errors[] = 'Bank info: upload the file or tick "To follow".';
if (count($assess) < 3 && !$in['assess_tf']) $errors[] = 'Assessments: upload all 3 files or tick "To follow".';
if (!$contract && !$in['contract_tf'])       $errors[] = 'Signed contract: upload the file or tick "To follow".';

if ($errors) {
    foreach (array_merge([$bank, $contract], $assess) as $f) if ($f) @unlink(UPLOAD_DIR . $f);
    back_with($errors, $in);
}

// If the document is complete, "to follow" no longer applies
$bankTf     = $bank ? 0 : 1;
$assessTf   = count($assess) >= 3 ? 0 : 1;
$contractTf = $contract ? 0 : 1;

// Step 3 defaults
$skillsValue = $in['skills'] ? implode(', ', $in['skills']) : 'N/A';
$nicheValue  = $in['other_niche'] !== '' ? $in['other_niche'] : 'N/A';

try {
    $stmt = db()->prepare(
        'INSERT INTO applicants
         (full_name, date_of_birth, address, email, contact_number, position,
          emergency_name, emergency_number, emergency_relationship,
          skills, other_niche, bank_info_file, assessment_file, contract_file,
          bank_to_follow, assessment_to_follow, contract_to_follow, ip_address)
         VALUES
         (:full_name, :dob, :address, :email, :contact, :position,
          :em_name, :em_number, :em_rel,
          :skills, :niche, :bank, :assess, :contract,
          :bank_tf, :assess_tf, :contract_tf, :ip)'
    );
    $stmt->execute([
        ':full_name'   => $in['full_name'],
        ':dob'         => $in['date_of_birth'],
        ':address'     => $in['address'],
        ':email'       => strtolower($in['email']),
        ':contact'     => $in['contact_number'],
        ':position'    => $in['position'],
        ':em_name'     => $in['emergency_name'],
        ':em_number'   => $in['emergency_number'],
        ':em_rel'      => $in['emergency_relationship'],
        ':skills'      => $skillsValue,
        ':niche'       => $nicheValue,
        ':bank'        => $bank,
        ':assess'      => $assess ? implode(',', $assess) : null,
        ':contract'    => $contract,
        ':bank_tf'     => $bankTf,
        ':assess_tf'   => $assessTf,
        ':contract_tf' => $contractTf,
        ':ip'          => $_SERVER['REMOTE_ADDR'] ?? null,
    ]);
} catch (Throwable $ex) {
    foreach (array_merge([$bank, $contract], $assess) as $f) if ($f) @unlink(UPLOAD_DIR . $f);
    error_log($ex->getMessage());

    $message = 'Something went wrong saving your information. Please try again.';
    if ($ex instanceof PDOException && stripos($ex->getMessage(), 'SQLSTATE[HY000] [2002]') !== false) {
        $message = 'The database is unavailable. Please start MySQL in XAMPP and try again.';
    }

    back_with([$message], $in);
}

unset($_SESSION['csrf']);
$_SESSION['submitted_name'] = $in['full_name'];
header('Location: success.php');
exit;