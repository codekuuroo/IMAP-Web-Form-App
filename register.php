<?php
header('Content-Type: text/plain');

$user = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$pass = $_POST['password'] ?? '';

// Profile Inputs
$Pname = filter_input(INPUT_POST, 'Pname', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$Addr = filter_input(INPUT_POST, 'Addr', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$CivStats = filter_input(INPUT_POST, 'CivStats', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$Bday = filter_input(INPUT_POST, 'Bdate', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$sex = filter_input(INPUT_POST, 'sex', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$religion = filter_input(INPUT_POST, 'religion', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$education = filter_input(INPUT_POST, 'education', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$job = filter_input(INPUT_POST, 'job', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$salary = filter_input(INPUT_POST, 'salary', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$philhealth = filter_input(INPUT_POST, 'philhealth', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// Relatives Arrays (from your dynamic JS cards)
$relName = $_POST['relativeName'] ?? [];
$relAge = $_POST['relativeAge'] ?? [];
$relCivStats = $_POST['CivStats'] ?? []; // Fixed to match the name="CivStats[]" in your relative HTML
$relPatient = $_POST['relPatient'] ?? [];
$relJob = $_POST['relJob'] ?? [];
$relIncome = $_POST['relIncome'] ?? [];

if (empty($user) || empty($pass) || empty($Pname)) {
    http_response_code(400);
    die("Please fill out all required account fields.");
}

$conn = new mysqli("localhost", "root", "", "IMAPForm");
if ($conn->connect_error) {
    http_response_code(500);
    die("Connection failed: " . $conn->connect_error);
}

// Check Username uniqueness
$checkUser = $conn->prepare("SELECT Patient_ID FROM users WHERE username = ?");
$checkUser->bind_param("s", $user);
$checkUser->execute();
$checkUser->store_result();
if ($checkUser->num_rows > 0) {
    http_response_code(400);
    die("Username is already taken.");
}
$checkUser->close();

// Generate Unique "PCXXXX" ID
$isUnique = false;
$PatientID = "";
while (!$isUnique) {
    $PatientID = "PC" . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
    $checkID = $conn->prepare("SELECT Patient_ID FROM users WHERE Patient_ID = ?");
    $checkID->bind_param("s", $PatientID);
    $checkID->execute();
    $checkID->store_result();
    if ($checkID->num_rows === 0) $isUnique = true;
    $checkID->close();
}

$conn->begin_transaction();

try {
    // 1. Insert User Account
    $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
    $stmtUser = $conn->prepare("INSERT INTO users (Patient_ID, username, password) VALUES (?, ?, ?)");
    $stmtUser->bind_param("sss", $PatientID, $user, $hashed_password);
    $stmtUser->execute();
    $stmtUser->close();

    // 2. Insert Profile Data
    $stmtPatient = $conn->prepare("INSERT INTO patient_information (Patient_ID, patient_Name, patient_Address, patient_CivilStatus, patient_BirthDate, patient_Sex, patient_Religion, patient_EducationalAttainment, patient_Job, patient_MonthlyIncome, philhealth_MembershipStatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtPatient->bind_param("sssssssssis", $PatientID, $Pname, $Addr, $CivStats, $Bday, $sex, $religion, $education, $job, $salary, $philhealth);
    $stmtPatient->execute();
    $stmtPatient->close();

    // 3. Insert Relative Data (if any were added)
    if (is_array($relName) && count($relName) > 0) {
        $stmtRel = $conn->prepare("INSERT INTO relative_information (Patient_ID, Relative_Name, Relative_Age, Relative_CivilStatus, Relative_RelationToPatient, Relative_Job, Relative_MonthlyIncome) VALUES (?, ?, ?, ?, ?, ?, ?)");
        for ($i = 0; $i < count($relName); $i++) {
            if (empty($relName[$i])) continue;
            $rName   = $relName[$i];
            $rAge    = intval($relAge[$i] ?? 0);
            $rCiv    = $relCivStats[$i] ?? '';
            $rPat    = $relPatient[$i] ?? '';
            $rJob    = $relJob[$i] ?? '';
            $rSalary = intval($relIncome[$i] ?? 0);

            $stmtRel->bind_param("ssisssi", $PatientID, $rName, $rAge, $rCiv, $rPat, $rJob, $rSalary);
            $stmtRel->execute();
        }
        $stmtRel->close();
    }

    $conn->commit();
    
    // THIS is the exact format your JavaScript is waiting for
    echo "SUCCESS:" . $PatientID;

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    die("Registration failed: " . $e->getMessage());
}
$conn->close();
?>