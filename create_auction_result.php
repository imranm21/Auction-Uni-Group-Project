<?php include_once("header.php") ?>
<?php require("utilities.php") ?>

<?php

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    // Redirect to the login page or handle the case when the user is not logged in
    header("Location: login.php");
    exit();
}

// Create new connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $seller_id = $_SESSION['ID'];
    $itemname = $_POST['auctionTitle'];
    $ItemDescription = $_POST['auctionDetails'];
    $category = $_POST['auctionCategory'];
    $startingprice = $_POST['auctionStartPrice'];
    $reservedprice = $_POST['auctionReservePrice'];
    $enddate = $_POST['auctionEndDate'];

    // SQL query
    $stmt = $conn->prepare("INSERT INTO auction (seller_id, ItemDescription, category, startingprice, reservedprice, itemname, enddate) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssdss", $seller_id, $ItemDescription, $category, $startingprice, $reservedprice, $itemname, $enddate);

    if ($stmt->execute()) {    
        // Get user email
        $user_email_query = "SELECT email FROM user WHERE user_id = {$_SESSION['ID']}";
        $stmt_user_email = $conn->prepare($user_email_query);
        $stmt_user_email->execute();
        $user_email_result = $stmt_user_email->get_result();
        $user_email_row = $user_email_result->fetch_assoc();
        $user_email = $user_email_row['email'];

        // //Send email to auctioneer
        $subject_message = 'Your auction has begun!';
        $body_message = 'Your auction for ' . $itemname . ' has been recorded. We will keep you notified of the process as it progresses.';
        $recipient_email = $user_email;
        send_email($subject_message, $body_message, $recipient_email);
        $stmt_user_email->close();

        $message = "Great, your item has now been put up for auction. You will receive email confirmation of this.";
    } else {
        $error_message = "Error: " . $stmt->error;
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    }

    // Close connection
    $stmt->close();
}

$conn->close();
?>

<html>

<body>
    <?php
    // Display success or error message, if any
    if (isset($message)) {
        echo ('<div class="text-center">Auction successfully created! <a href="mylistings.php">View your new listing.</a></div>');
    }
    if (isset($error_message)) {
        echo "<p>$error_message</p>";
    }
    ?>

</body>

</html>

<?php include_once("footer.php") ?>