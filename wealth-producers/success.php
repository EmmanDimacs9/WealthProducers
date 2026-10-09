<?php
require __DIR__ . '/config.php';
$name = $_SESSION['submitted_name'] ?? '';
unset($_SESSION['submitted_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Submitted | Wealth Producers</title>
<link rel="icon" href="assets/logo.png">
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700&family=Inter:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="done-page">
  <div class="done">
    <img src="assets/logo.png" alt="Wealth Producers" class="done-logo">
    <h1>THANK YOU<?= $name ? ', ' . e(strtoupper(explode(' ', $name)[0])) : '' ?>!</h1>
    <p>Your information has been submitted successfully.<br>All information is kept confidential.</p>
    <a class="btn" href="index.php">BACK TO FORM</a>
  </div>
</body>
</html>
