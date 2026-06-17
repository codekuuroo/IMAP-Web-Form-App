<?php
session_start();
header('Content-Type: text/plain');

$user = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$pass = $_POST['password'] ?? '';

$conn = new mysqli("localhost", "root", "", "IMAPForm");
if ($conn->connect_error) {
    http_response_code(500);
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT Patient_ID, password FROM account_information WHERE username = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    http_response_code(401);
    die("Invalid credentials.");
}

$stmt->bind_result($PatientID, $hashedPassword);
$stmt->fetch();

if (password_verify($pass, $hashedPassword)) {
    $_SESSION['Patient_ID'] = $PatientID;
    $_SESSION['username'] = $user;
    echo "SUCCESS|" . $PatientID;
} else {
    http_response_code(401);
    die("Invalid credentials.");
}
$stmt->close();
$conn->close();
?>