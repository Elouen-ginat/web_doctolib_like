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
  $result = mysqli_query($conn, $query);
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

  $username = mysqli_real_escape_string($conn, stripslashes($username));
  $password = mysqli_real_escape_string($conn, stripslashes($password));
  $user_type = getStatus($conn, $username, $password);
  if ($user_type == 'admin') {
    $query = "SELECT * FROM client";
    $result = mysqli_query($conn, $query) or die(mysqli_error($conn));
    $response = mysqli_fetch_all($result, MYSQLI_ASSOC);
    header('Content-Type: application/json');
    http_response_code(200);
    echo json_encode($response, JSON_PRETTY_PRINT);
  } else {
    $error = array("error" => "Need to be an admin");
    http_response_code(500);
    echo json_encode($error, JSON_PRETTY_PRINT);
  }
}

?>