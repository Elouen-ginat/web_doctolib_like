<?php
// Connect to Database
include("../config.php");
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
  case 'GET':
    if (!empty($_GET["username"]) && !empty($_GET["password"])) {
      // Get clients
      getClients($_GET["username"], $_GET["password"]);
    } else {
      $error = array("error" => "Need authentification");
      http_response_code(500);
      echo json_encode($error, JSON_PRETTY_PRINT);
    }
    break;
  case 'POST':
    // Change status enable
    setClientEnable();
    break;
  default:
    // invalid request
    header("HTTP/1.0 405 Method Not Allowed");
    break;
}
?>

<?php

function verifyLogin($username, $password)
{
  global $conn;
  $query = "SELECT * FROM login WHERE username='" . $username . "' AND password='" . $password . "'";
  $result = mysqli_query($conn, $query) or die(mysqli_error($conn));
  $row = mysqli_num_rows($result);
  if ($row == 1) {
    return true;
  } else {
    return false;
  }
}

function getStatus($conn, $username, $password)
{
  $query = "SELECT * FROM `login` INNER JOIN `client` ON login.user_id = client.user_id WHERE username='$username' and password='$password'";
  $result = mysqli_query($conn, $query) or die(mysqli_error($conn));
  $client_row = mysqli_num_rows($result);

  $query = "SELECT * FROM `login` INNER JOIN `doctor` ON login.user_id = doctor.user_id WHERE username='$username' and password='$password'";
  $result = mysqli_query($conn, $query) or die(mysqli_error($conn));
  $doctor_row = mysqli_num_rows($result);

  if ($client_row == 1) {
    return 'client';
  } else if ($doctor_row == 1) {
    return 'doctor';
  } else if (verifyLogin($username, $password)) {
    return 'admin';
  }
  return null;
}

function getClients($username, $password)
{
  global $conn;
  header('Content-Type: application/json');
  $username = mysqli_real_escape_string($conn, stripslashes($username));
  $password = mysqli_real_escape_string($conn, stripslashes($password));
  $user_type = getStatus($conn, $username, $password);
  if ($user_type == 'admin') {
    $query = "SELECT * FROM client INNER JOIN login ON client.user_id = login.user_id";
    $result = mysqli_query($conn, $query) or die(mysqli_error($conn));
    $response = mysqli_fetch_all($result, MYSQLI_ASSOC);
    if ($response) {
      http_response_code(200);
      echo json_encode($response, JSON_PRETTY_PRINT);
    } else {
      $error = array("error" => "No client found");
      http_response_code(500);
      echo json_encode($error, JSON_PRETTY_PRINT);
    }
  } else {
    $error = array("error" => "Need to be an admin");
    http_response_code(500);
    echo json_encode($error, JSON_PRETTY_PRINT);
  }
}

function setClientEnable()
{
  global $conn;
  header('Content-Type: application/json');
  $username = mysqli_real_escape_string($conn, stripslashes($_POST["username"]));
  $password = mysqli_real_escape_string($conn, stripslashes($_POST["password"]));
  $client_id = mysqli_real_escape_string($conn, stripslashes($_POST["client_id"]));
  $enabled = mysqli_real_escape_string($conn, stripslashes($_POST["enabled"]));

  $user_type = getStatus($conn, $username, $password);
  if ($user_type == 'admin') {
    $query = "UPDATE login INNER JOIN client ON login.user_id = client.user_id SET enabled='$enabled' WHERE client_id='$client_id'";
    $result = mysqli_query($conn, $query) or die(mysqli_error($conn));
    if ($result) {
      $response = array(
        'status' => 200,
        'status_message' => 'Client updated successfully'
      );
      echo json_encode($response, JSON_PRETTY_PRINT);
    } else {
      $response = array(
        'status' => 500,
        'status_message' => 'Error updating client'
      );
      echo json_encode($response, JSON_PRETTY_PRINT);
    }
  } else {
    $response = array(
      'status' => 500,
      'status_message' => 'Need to be an admin'
    );
    echo json_encode($response, JSON_PRETTY_PRINT);
  }
}

?>