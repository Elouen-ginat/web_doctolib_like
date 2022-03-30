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
                    $str_date = new DateTime($_GET["str_date"]);
                    $end_date = new DateTime($_GET["end_date"]);
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

function getAppointmentsDate($doctor_id, $str_date, $end_date)
{
    global $conn;
    header('Content-Type: application/json');
    // Get str_hour and end_hour of doctor
    $query = "SELECT str_hour, end_hour FROM doctor WHERE doctor_id=" . $doctor_id . " LIMIT 1";
    $result = mysqli_query($conn, $query);
    $doctor = mysqli_fetch_assoc($result);
    if ($doctor != null) {
        $str_hour = new DateTime($doctor["str_hour"]);
        $end_hour = new DateTime($doctor["end_hour"]);
    } else {
        $error = array("id" => "$doctor_id", "error" => "No doctor found");
        http_response_code(500);
        echo json_encode($error, JSON_PRETTY_PRINT);
        return;
    }
    // Get all appointments interval hours
    $date = $str_date;
    $appointments = array();
    $max_iter = 1000000;
    while ($date <= $end_date) {
        $hour = DateTime::createFromFormat("Y-m-d H:i:s", $date->format("Y-m-d") . $str_hour->format("H:i:s"));
        $end_date_hour = DateTime::createFromFormat("Y-m-d H:i:s", $date->format("Y-m-d") . $end_hour->format("H:i:s"));
        while ($hour <= $end_date_hour) {
            $key = $hour->format("Y-m-d H:i:s");
            $appointments["$key"] = null;
            date_add($hour, date_interval_create_from_date_string(APPTMENT_DUR . " sec"));
            $max_iter = $max_iter - 1;
            if ($max_iter < 0) {
                $error = array("error" => "Too much value to return");
                http_response_code(500);
                echo json_encode($error, JSON_PRETTY_PRINT);
                return;
            }
        }
        date_add($date, date_interval_create_from_date_string('1 day'));
    }


    // Get all doctor's appointments
    $query = "SELECT client_id, datetime FROM appointment WHERE doctor_id=" . $doctor_id;
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $datetime = $row["datetime"];
        $client_id = $row["client_id"];
        $appointments["$datetime"] = $client_id;
    }
    http_response_code(200);
    echo json_encode($appointments, JSON_PRETTY_PRINT);
}

function AddAppointment()
{
    header('Content-Type: application/json');
    global $conn;
    $doctor_id = $_POST["doctor_id"];
    $client_id = $_POST["client_id"];
    $datetime = $_POST["datetime"];

    echo $query = "INSERT INTO `appointment`(`doctor_id`, `client_id`, `datetime`) VALUES ('$doctor_id','$client_id','$datetime')";
    if (mysqli_query($conn, $query)) {
        $response = array(
            'status' => 200,
            'status_message' => 'RDV ajoute avec succes.'
        );
    } else {
        $response = array(
            'status' => 500,
            'status_message' => 'erreur. ' . mysqli_error($conn)
        );
    }
    header('Content-Type: application/json');
    echo json_encode($response);
}

?>
