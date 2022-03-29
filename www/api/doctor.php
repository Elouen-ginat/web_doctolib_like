<?php
// Connect to Database
include("../config.php");
$request_method = $_SERVER["REQUEST_METHOD"];

switch ($request_method) {
  case 'GET':
    if (!empty($_GET["id"])) {
      // Get on doctor
      $id = intval($_GET["id"]);
      getDoctor($id);
    } else {
      // Get all doctors
      getDoctors();
    }
    break;
  default:
    // invalid request
    header("HTTP/1.0 405 Method Not Allowed");
    break;
}
?>

<?php

function getDoctors()
{
  global $conn;
  $query = "SELECT * FROM doctor";
  $response = array();
  $result = mysqli_query($conn, $query);
  while ($row = mysqli_fetch_array($result)) {
    $response[] = $row;
  }
  header('Content-Type: application/json');
  echo json_encode($response, JSON_PRETTY_PRINT);
}

function getDoctor($id = 0)
{
  global $conn;
  $query = "SELECT * FROM doctor";
  if ($id != 0) {
    $query .= " WHERE doctor_id=" . $id . " LIMIT 1";
  }
  $response = array();
  $result = mysqli_query($conn, $query);
  while ($row = mysqli_fetch_array($result)) {
    $response[] = $row;
  }
  header('Content-Type: application/json');
  echo json_encode($response, JSON_PRETTY_PRINT);
}

?>