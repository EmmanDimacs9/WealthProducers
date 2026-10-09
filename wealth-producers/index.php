<?php require __DIR__ . '/config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Information Form | Wealth Producers</title>
<link rel="icon" href="assets/logo.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="layout">

  <!-- LEFT PANEL -->
  <aside class="side">
    <div class="brand">
      <img src="assets/logo.png" alt="Wealth Producers logo">
      <div class="brand-text"><span>WEALTH</span><b>PRODUCERS</b></div>
    </div>

    <div class="side-main">
      <h1>INFORMATION<br>FORM</h1>
      <p>Please provide the required information and submit the form.</p>
      <ol class="steps" id="stepList">
        <li class="active" data-step="1"><i>1</i><span>Personal Information</span></li>
        <li data-step="2"><i>2</i><span>Emergency Contact</span></li>
        <li data-step="3"><i>3</i><span>Specific Skills</span></li>
        <li data-step="4"><i>4</i><span>Document Upload</span></li>
      </ol>
    </div>

    <footer>&copy; <?= date('Y') ?> Wealth Producers. All information is kept confidential.</footer>
  </aside>

  <!-- RIGHT PANEL -->
  <main class="main">
    <form id="infoForm" action="submit.php" method="post" enctype="multipart/form-data" novalidate>
      <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
      <div class="hp"><label>Leave blank<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>

      <div class="progress"><div class="bar" id="bar"></div></div>
      <div class="stepline" id="stepLine"></div>

      <?php if (!empty($_SESSION['errors'])): ?>
        <div class="alert error">
          <?php foreach ($_SESSION['errors'] as $er): ?><div><?= e($er) ?></div><?php endforeach; ?>
        </div>
        <?php unset($_SESSION['errors']); ?>
      <?php endif; ?>

      <?php $old = $_SESSION['old'] ?? []; unset($_SESSION['old']); ?>

      <!-- STEP 1 -->
      <section class="panel active" data-step="1">
        <h2>SUBMIT INFORMATION</h2>
        <p class="sub">Please complete all fields below.</p>

        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" placeholder="Full Name" maxlength="150" required value="<?= e($old['full_name'] ?? '') ?>">

        <label for="dob">Date of Birth</label>
        <input type="date" id="dob" name="date_of_birth" required max="<?= date('Y-m-d') ?>" value="<?= e($old['date_of_birth'] ?? '') ?>">

        <label for="address">Current Address</label>
        <input type="text" id="address" name="address" placeholder="Current Address" maxlength="255" required value="<?= e($old['address'] ?? '') ?>">

        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="yourname@gmail.com" maxlength="150" required
               pattern="[A-Za-z0-9._%+\-]+@[Gg][Mm][Aa][Ii][Ll]\.[Cc][Oo][Mm]" data-gmail
               value="<?= e($old['email'] ?? '') ?>">

        <label for="contact">Contact Number</label>
        <input type="tel" id="contact" name="contact_number" placeholder="+63 09XXXXXXXXX" inputmode="numeric"
               maxlength="11" pattern="09[0-9]{9}" data-phone required value="<?= e($old['contact_number'] ?? '') ?>">

        <label for="position">Position</label>
        <select id="position" name="position" required>
          <option value="" disabled <?= empty($old['position']) ? 'selected' : '' ?>>Position</option>
          <?php foreach (POSITIONS as $p): ?>
            <option value="<?= e($p) ?>" <?= ($old['position'] ?? '') === $p ? 'selected' : '' ?>><?= e($p) ?></option>
          <?php endforeach; ?>
        </select>

        <div class="actions"><button type="button" class="btn" data-next>NEXT</button></div>
      </section>

      <!-- STEP 2 -->
      <section class="panel" data-step="2">
        <h2>EMERGENCY CONTACT</h2>
        <p class="sub">Who should we reach out to in case of emergency?</p>

        <label for="em_name">Name of Contact Person</label>
        <input type="text" id="em_name" name="emergency_name" placeholder="Name of Contact Person" maxlength="150" required value="<?= e($old['emergency_name'] ?? '') ?>">

        <label for="em_number">Contact Number</label>
        <input type="tel" id="em_number" name="emergency_number" placeholder="+63 09XXXXXXXXX" inputmode="numeric"
               maxlength="11" pattern="09[0-9]{9}" data-phone required value="<?= e($old['emergency_number'] ?? '') ?>">

        <label for="em_rel">Relationship</label>
        <select id="em_rel" name="emergency_relationship" required>
          <option value="" disabled <?= empty($old['emergency_relationship']) ? 'selected' : '' ?>>Relationship</option>
          <?php foreach (RELATIONSHIPS as $r): ?>
            <option value="<?= e($r) ?>" <?= ($old['emergency_relationship'] ?? '') === $r ? 'selected' : '' ?>><?= e($r) ?></option>
          <?php endforeach; ?>
        </select>

        <div class="actions two">
          <button type="button" class="btn ghost" data-back>BACK</button>
          <button type="button" class="btn" data-next>NEXT</button>
        </div>
      </section>

      <!-- STEP 3 -->
      <section class="panel" data-step="3">
        <h2>SPECIFIC SKILLS</h2>
        <p class="sub">Select all that apply. If none, leave unchecked and it will be saved as N/A.</p>

        <div class="skills">
          <?php foreach (SKILLS as $i => $s): $sel = in_array($s, $old['skills'] ?? [], true); ?>
            <label class="chip">
              <input type="checkbox" name="skills[]" value="<?= e($s) ?>" <?= $sel ? 'checked' : '' ?>>
              <span><?= e(strtoupper($s)) ?></span>
            </label>
          <?php endforeach; ?>
        </div>

        <label for="niche">Any other niche you want to explore?</label>
        <input type="text" id="niche" name="other_niche" placeholder="Other niche (blank = N/A)" maxlength="255" value="<?= e($old['other_niche'] ?? '') ?>">

        <div class="actions two">
          <button type="button" class="btn ghost" data-back>BACK</button>
          <button type="button" class="btn" data-next>NEXT</button>
        </div>
      </section>

      <!-- STEP 4 -->
      <section class="panel" data-step="4">
        <h2>UPLOAD DOCUMENTS</h2>
        <p class="sub">Upload each document, or tick <b>To follow</b> if you don't have it yet.</p>

        <div class="file">
          <label for="bank">Bank Info <em>JPEG / PNG</em></label>
          <input type="file" id="bank" name="bank_info" accept="image/jpeg,image/png" data-need="1">
          <label class="tf"><input type="checkbox" name="bank_tf" value="1" data-tf <?= !empty($old['bank_tf']) ? 'checked' : '' ?>> To follow</label>
          <div class="file-msg"></div>
        </div>

        <div class="file">
          <label for="assess">Assessments <em>JPEG / PNG &middot; 3 files required</em></label>
          <input type="file" id="assess" name="assessment[]" accept="image/jpeg,image/png" multiple data-need="3">
          <small class="file-count"></small>
          <label class="tf"><input type="checkbox" name="assess_tf" value="1" data-tf <?= !empty($old['assess_tf']) ? 'checked' : '' ?>> To follow (tick if you have fewer than 3 files)</label>
          <div class="file-msg"></div>
        </div>

        <div class="file">
          <label for="contract">Signed Contract <em>PDF</em></label>
          <input type="file" id="contract" name="contract" accept="application/pdf" data-need="1">
          <label class="tf"><input type="checkbox" name="contract_tf" value="1" data-tf <?= !empty($old['contract_tf']) ? 'checked' : '' ?>> To follow</label>
          <div class="file-msg"></div>
        </div>
        <p class="hint">Max 5 MB per file. If the form reloads with an error, please re-select your files.</p>

        <div class="actions two">
          <button type="button" class="btn ghost" data-back>BACK</button>
          <button type="submit" class="btn">SUBMIT</button>
        </div>
      </section>
    </form>
  </main>
</div>
<script src="assets/app.js"></script>
</body>
</html>
