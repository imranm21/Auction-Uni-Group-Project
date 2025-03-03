<?php

if (!isset($_POST['functionname']) || !isset($_POST['arguments'])) {
  return;
}

// Get the user ID from the session
$arguments = $_POST['arguments'];
$item_id = $arguments['item_id'];
$buyer_id = $arguments['buyer_id'];

$servername = "127.0.0.1";
$username = "root";
$password = "admin123";
$database = "test2";

// Create new connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if ($_POST['functionname'] == "add_to_watchlist") {

  // INSERT INTO wishlist
  $stmt = $conn->prepare("INSERT INTO wishlist (auction_id, buyer_id) VALUES (?, ?)");
  $stmt->bind_param("ii", $item_id, $buyer_id); // Assuming both fields are integers

} else if ($_POST['functionname'] == "remove_from_watchlist") {

  // DELETE from wishlist
  $stmt = $conn->prepare("DELETE FROM wishlist WHERE auction_id=? AND buyer_id=?");
  $stmt->bind_param("ii", $item_id, $buyer_id); // Assuming both fields are integers
}

if ($stmt->execute()) {
  $res = "success";
} else {
  $res = "error: " . $stmt->error;
}

// Close connection
$stmt->close();
$conn->close();


// Note: Echoing from this PHP function will return the value as a string.
// If multiple echo's in this file exist, they will concatenate together,
// so be careful. You can also return JSON objects (in string form) using
// echo json_encode($res).
echo $res;
