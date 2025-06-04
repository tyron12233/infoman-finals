<?php
$passwordToHash = 'admin';
$hashedPassword = password_hash($passwordToHash, PASSWORD_DEFAULT);
echo "Username: admin\n";
echo "Hashed Password for 'admin': " . $hashedPassword . "\n";
// Example output: Hashed Password for 'admin': $2y$10$xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
?>