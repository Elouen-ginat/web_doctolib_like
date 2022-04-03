<?php
// Connect to Database
include("../config.php");
$request_method = $_SERVER["REQUEST_METHOD"];

// Parse
switch ($request_method) {
    case 'GET':
        if (!empty($_GET["id"])) {
            // Get id doctor
            $id = intval($_GET["id"]);
            if (!empty($_GET["str_date"]) && !empty($_GET["end_date"])) {
                try {
                    $str_date = new DateTime(mysqli_real_escape_string($conn, $_GET["str_date"]));
                    $end_date = new DateTime(mysqli_real_escape_string($conn, $_GET["end_date"]));
                } catch (Exception $e) {
                    $error = array("id" => "$id", "error" => "Dates are not valid");
                    http_response_code(500);
                    echo json_encode($error, JSON_PRETTY_PRINT);
                    return;
                }
                getAppointmentsDate($id, $str_date, $end_date);
            } else {
                getAppointment($id);
            }
        } else {
            // Get all doctors
            getAppointments();
        }
        break;
    case 'POST':
        // Ajouter un produit
        AddAppointment();
        break;
    default:
        // invalid request
        header("HTTP/1.0 405 Method Not Allowed");
        break;
}
?>

<?php

function getAppointments()
{
    header('Content-Type: application/json');
    global $conn;
    $query = "SELECT * FROM appointment";
    $response = array();
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_array($result)) {
        $response[] = $row;
    }
    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);
}

function getAppointment($client_id = 0)
{
    header('Content-Type: application/json');
    global $conn;
    $query = "SELECT * FROM appointment";
    if ($client_id != 0) {
        $query .= " WHERE client_id=" . $client_id;
    } else {
        $error = array("id" => "$client_id", "error" => "No client found");
        http_response_code(500);
        echo json_encode($error, JSON_PRETTY_PRINT);
        return;
    }
    $response = array();
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_array($result)) {
        $response[] = $row;
    }
    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);
}

// Get str_hour and end_hour of doctor
function getWorkingInfo($doctor_id = 0)
{
    global $conn;
    $query = "SELECT str_hour, end_hour, activity_days FROM doctor WHERE doctor_id=" . $doctor_id . " LIMIT 1";
    $result = mysqli_query($conn, $query);
    $doctor = mysqli_fetch_assoc($result);
    if ($doctor != null) {
        return array(
            "str_hour" => new DateTime($doctor["str_hour"]),
            "end_hour" => new DateTime($doctor["end_hour"]),
            "activity_days" => json_decode($doctor["activity_days"], true)
        );
    } else {
        return null;
    }
}

function getAppointmentsDate($doctor_id, $str_date, $end_date)
{
    global $conn;
    header('Content-Type: application/json');
    // Get str_hour and end_hour of doctor
    $doctor_info = getWorkingInfo($doctor_id);
    if ($doctor_info == null) {
        $error = array("id" => "$doctor_id", "error" => "No doctor found");
        http_response_code(500);
        echo json_encode($error, JSON_PRETTY_PRINT);
        return;
    } else {
        $str_hour = $doctor_info["str_hour"];
        $end_hour = $doctor_info["end_hour"];
        $activity_days = $doctor_info["activity_days"];
    }
    // Get all appointments interval hours
    $date = $str_date;
    $appointments = array();
    $max_iter = 100000;
    while ($date <= $end_date) {
        // Test si la date est un jour ou travaille le docteur
        if (array_key_exists(strtolower($date->format("l")), $activity_days)) {
            $hour = DateTime::createFromFormat("Y-m-d H:i:s", $date->format("Y-m-d") . $str_hour->format("H:i:s"));
            $end_date_hour = DateTime::createFromFormat("Y-m-d H:i:s", $date->format("Y-m-d") . $end_hour->format("H:i:s"));
            while ($hour <= $end_date_hour) {
                $key = $hour->format("Y-m-d H:i:s");
                $appointments[$key] = null;
                date_add($hour, date_interval_create_from_date_string(APPTMENT_DUR . " sec"));
                $max_iter = $max_iter - 1;
                if ($max_iter < 0) {
                    $error = array("error" => "Too much value to return");
                    http_response_code(500);
                    echo json_encode($error, JSON_PRETTY_PRINT);
                    return;
                }
            }
        } else {
            $key = $date->format("Y-m-d");
            $appointments[$key] = null;
        }
        date_add($date, date_interval_create_from_date_string('1 day'));
    }


    // Get all doctor's appointments
    $query = "SELECT client_id, datetime FROM appointment WHERE doctor_id=" . $doctor_id;
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $datetime = $row["datetime"];
        $client_id = $row["client_id"];
        if (array_key_exists($datetime, $appointments)) {
            $appointments[$datetime] = $client_id;
        }
    }
    http_response_code(200);
    echo json_encode($appointments, JSON_PRETTY_PRINT);
}

function getClientID($username, $password)
{
    global $conn;
    $query = "SELECT client_id FROM `login` INNER JOIN `client` ON login.user_id WHERE username='$username' and password='$password'";
    $result = mysqli_query($conn, $query) or die(mysqli_error($conn));
    $client_row = mysqli_fetch_assoc($result);
    if ($client_row != null) {
        return $client_row["client_id"];
    } else {
        return null;
    }
}

function AddAppointment()
{
    global $conn;
    header('Content-Type: application/json');
    $doctor_id = mysqli_real_escape_string($conn, stripslashes($_POST["doctor_id"]));
    $datetime = new DateTime(mysqli_real_escape_string($conn, stripslashes($_POST["datetime"])));
    $username = mysqli_real_escape_string($conn, stripslashes($_POST["username"]));
    $password = mysqli_real_escape_string($conn, stripslashes($_POST["password"]));
    // Get str_hour and end_hour of doctor
    $doctor_info = getWorkingInfo($doctor_id);
    if ($doctor_info == null) {
        $response = array(
            'status' => 200,
            'status_message' => 'No doctor found.'
        );
        echo json_encode($response);
        return;
    } else {
        $str_hour = $doctor_info["str_hour"];
        $end_hour = $doctor_info["end_hour"];
        $activity_days = $doctor_info["activity_days"];
    }

    // Test if the appointment is in the working hours
    $actual_hour = new DateTime($datetime->format("H:i:s"));
    if ($actual_hour < $str_hour || $actual_hour > $end_hour) {
        $response = array(
            'status' => 500,
            'status_message' => 'The appointment is not in the working hours.'
        );
        echo json_encode($response);
        return;
    }

    // Test if the appointment is in the working days
    if (!array_key_exists(strtolower($datetime->format("l")), $activity_days)) {
        $response = array(
            'status' => 500,
            'status_message' => 'The appointment is not in the working days.'
        );
        echo json_encode($response);
        return;
    }

    // Test if you are a client
    $client_id = getClientID($username, $password);
    if ($client_id == null) {
        $response = array(
            'status' => 500,
            'status_message' => 'You are not a client.'
        );
        echo json_encode($response);
        return;
    }

    // Add appointment
    $datetime = $datetime->format("Y-m-d H:i:s");
    $query = "INSERT INTO `appointment`(`doctor_id`, `client_id`, `datetime`) VALUES ('$doctor_id','$client_id','$datetime')";
    if (mysqli_query($conn, $query)) {
        $response = array(
            'status' => 200,
            'status_message' => 'Appointment added.'
        );
        echo json_encode($response);
        return;
    } else {
        $response = array(
            'status' => 500,
            'status_message' => 'Error: ' . mysqli_error($conn)
        );
        echo json_encode($response);
        return;
    }
}

?>
