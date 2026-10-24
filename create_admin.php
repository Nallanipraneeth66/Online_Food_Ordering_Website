<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not found.');
}

$username = getenv('ADMIN_USERNAME');
$password = getenv('ADMIN_PASSWORD');

if ($username === false || $username === '' || strlen($username) > 100) {
    fwrite(STDERR, "Set ADMIN_USERNAME to a value between 1 and 100 characters.\n");
    exit(1);
}

if ($password === false || strlen($password) < 12) {
    fwrite(STDERR, "Set ADMIN_PASSWORD to a value of at least 12 characters.\n");
    exit(1);
}

require __DIR__ . '/db.php';

$query = "SELECT id FROM admins WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    fwrite(STDERR, "An admin with that username already exists.\n");
    exit(1);
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$insertQuery = "INSERT INTO admins (username, password) VALUES (?, ?)";
$insertStmt = $conn->prepare($insertQuery);
$insertStmt->bind_param("ss", $username, $hashedPassword);

if (!$insertStmt->execute()) {
    fwrite(STDERR, "Failed to create admin: " . $insertStmt->error . "\n");
    exit(1);
}

echo "Admin user created successfully.\n";
