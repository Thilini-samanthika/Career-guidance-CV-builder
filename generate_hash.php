<?php

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
if (password_verify($password, $row['password'])) {
    // login success
}

// Normal User password
echo "Normal user hash: " . password_hash("pass123", PASSWORD_DEFAULT) . "\n";

// Admin password
echo "Admin hash: " . password_hash("admin123", PASSWORD_DEFAULT) . "\n";

// Company password
echo "Company hash: " . password_hash("comp123", PASSWORD_DEFAULT) . "\n";
?>

