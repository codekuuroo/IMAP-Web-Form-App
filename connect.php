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
    die('Connect Error ('. $conn->connect_errno .') '.$conn->connect_error);
} else {
    $stmt1 = $conn->prepare("INSERT INTO patient_information (Patient_ID, patient_Name, patient_Address, patient_CivilStatus, patient_BirthDate, patient_Sex, patient_Religion, patient_EducationalAttainment, patient_Job, patient_MonthlyIncome, philhealth_MembershipStatus) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt2 = $conn->prepare("INSERT INTO relative_information (Patient_ID, Relative_Name, Relative_Age, Relative_CivilStatus, Relative_RelationToPatient, Relative_Job, Relative_MonthlyIncome) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt3 = $conn->prepare("INSERT INTO application_information (Patient_ID, Patient_Diagnosis, Patient_NatureOfRequest) VALUES (?, ?, ?)");
    
    if($stmt1 === false) {
        die('Prepare Error: ' . $conn->error);
    }

    $stmt1->bind_param("sssssssssis", $PatientID, $Pname, $Addr, $CivStats, $Bday, $sex, $religion, $education, $job, $salary, $philhealth);
    
    if ($stmt1->execute()) {
        echo "Patient record inserted successfully.<br>";
        
        if (is_array($relName)) {
            for ($i = 0; $i < count($relName); $i++) {
                $stmt2->bind_param("ssisssi", $PatientID, $relName[$i], $relAge[$i], $relCivStats[$i], $relPatient[$i], $relJob[$i], $relIncome[$i]);
                if (!$stmt2->execute()) {
                    echo "Error inserting relative: " . $stmt2->error . "<br>";
                    break;
                }
            }
        }

        if ($stmt3) {
            $stmt3->bind_param("sss", $PatientID, $diagnosis, $natureOfRequest);
            if (!$stmt3->execute()) {
                echo "Error inserting application information: " . $stmt3->error . "<br>";
            }
        } else {
            echo "Error preparing application information statement: " . $conn->error . "<br>";
        }
    } else {
        echo "Error inserting patient: " . $stmt1->error;
    }

    

    if ($stmt1) {
        $stmt1->close();
    }
    if ($stmt2) {
        $stmt2->close();
    }
    if ($stmt3) {
        $stmt3->close();
    }
    $conn->close();
}
?>
