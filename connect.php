<?php

$PatientID = filter_input(INPUT_POST, 'PatientID', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
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
$relName = $_POST['relativeName'] ?? [];
$relAge = $_POST['relativeAge'] ?? [];
$relCivStats = $_POST['relCivStats'] ?? [];
$relPatient = $_POST['relPatient'] ?? [];
$relJob = $_POST['relJob'] ?? [];
$relIncome = $_POST['relIncome'] ?? [];
$diagnosis = filter_input(INPUT_POST, 'diagnosis', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$Request = filter_input(INPUT_POST, 'request', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$otherRequest = filter_input(INPUT_POST, 'otherRequest', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
$natureOfRequest = $Request . $otherRequest;

$host = "localhost";
$dbusername = "root";
$dbpassword = "";
$dbname = "IMAP_Form";

$conn = new mysqli($host, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    // Send a 500 error code so JS catch block receives the actual error text
    http_response_code(500);
    die('Database Connection Error: '. $conn->connect_error);
}

// 1. Prepare statements
$stmt1 = $conn->prepare("INSERT INTO patient_information (Patient_ID, patient_Name, patient_Address, patient_CivilStatus, patient_BirthDate, patient_Sex, patient_Religion, patient_EducationalAttainment, patient_Job, patient_MonthlyIncome, philhealth_MembershipStatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt2 = $conn->prepare("INSERT INTO relative_information (Patient_ID, Relative_Name, Relative_Age, Relative_CivilStatus, Relative_RelationToPatient, Relative_Job, Relative_MonthlyIncome) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt3 = $conn->prepare("INSERT INTO application_information (Patient_ID, Patient_Diagnosis, Patient_NatureOfRequest) VALUES (?, ?, ?)");

// 2. Strict verification check on ALL prepared statements
if ($stmt1 === false || $stmt2 === false || $stmt3 === false) {
    http_response_code(500);
    die('SQL Prepare Error. Check if table/column names match exactly! MySQL Error: ' . $conn->error);
}

// 3. Bind and execute Patient Info
$stmt1->bind_param("sssssssssis", $PatientID, $Pname, $Addr, $CivStats, $Bday, $sex, $religion, $education, $job, $salary, $philhealth);

if ($stmt1->execute()) {
    $relativeErrors = false;
    
    if (is_array($relName) && count($relName) > 0) {
        for ($i = 0; $i < count($relName); $i++) {
            // Ensure inputs exist at index to avoid undefined index notices
            $rName   = $relName[$i] ?? '';
            $rAge    = intval($relAge[$i] ?? 0);
            $rCiv    = $relCivStats[$i] ?? '';
            $rPat    = $relPatient[$i] ?? '';
            $rJob    = $relJob[$i] ?? '';
            $rSalary = intval($relIncome[$i] ?? 0);

            $stmt2->bind_param("ssisssi", $PatientID, $rName, $rAge, $rCiv, $rPat, $rJob, $rSalary);
            if (!$stmt2->execute()) {
                $relativeErrors = true;
                break;
            }
        }
    }

    // 4. Execute Application Info
    $stmt3->bind_param("sss", $PatientID, $diagnosis, $natureOfRequest);
    $stmt3->execute();

    if (!$relativeErrors) {
        echo "SUCCESS";
    } else {
        echo "PARTIAL_FAILURE_ON_RELATIVES";
    }
} else {
    http_response_code(500);
    echo "DATABASE_WRITE_ERROR: " . $stmt1->error;
}

// Close connections safely
$stmt1->close();
$stmt2->close();
$stmt3->close();
$conn->close();
?>
