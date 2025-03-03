<?php include_once("header.php") ?>
<?php require("utilities.php") ?>

<?php

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    // Redirect to the login page or handle the case when the user is not logged in
    header("Location: login.php");
    exit();
}

// Get the user ID from the session
$buyer_id = $_SESSION['ID'];

// Get user email
$user_email_query = "SELECT email FROM user WHERE user_id = {$_SESSION['ID']}";
$stmt_user_email = $conn->prepare($user_email_query);
$stmt_user_email->execute();
$user_email_result = $stmt_user_email->get_result();
$user_email_row = $user_email_result->fetch_assoc();
$user_email = $user_email_row['email'];
$stmt_user_email->close();

// Create new connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {

    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Retrieve amount from form
    $amount = $_POST['amount'];

    // Get auction id from form
    $auction_id = $_POST['auction_id'];

    // Insert form results into DB
    $stmt = $conn->prepare("INSERT INTO bid (amount, buyer_id, auction_id) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $amount, $buyer_id, $auction_id);

    //If this is successfully inserted...
    if ($stmt->execute()) {

        // Retrieve the highest bid from the database using prepared statement
        $highest_bid_query = "SELECT * FROM bid WHERE auction_id = ? ORDER BY amount DESC LIMIT 1";
        $stmt_highest_bid = $conn->prepare($highest_bid_query);
        $stmt_highest_bid->bind_param("i", $auction_id);
        $stmt_highest_bid->execute();
        $highest_bid_result = $stmt_highest_bid->get_result();

        if ($highest_bid_result->num_rows > 0) {
            $row = $highest_bid_result->fetch_assoc();
            $highest_bid_amount = $row['amount'];

            // Try-catch block for handling highest bid logic
            try {
                // Check if the bid just placed is the highest bid
                if ($amount >= $highest_bid_amount) {

                    // Inform the user
                    echo 'Congratulations! You have the highest bid. You will receive email confirmation of this as well.';

                    // Get second highest bid
                    $second_highest_bid_query = "SELECT * FROM bid INNER JOIN user ON bid.buyer_id = user.user_id WHERE auction_id = ? AND amount < ? ORDER BY amount DESC LIMIT 1";
                    $stmt_second_highest_bid = $conn->prepare($second_highest_bid_query);
                    $stmt_second_highest_bid->bind_param("ii", $auction_id, $highest_bid_amount);
                    $stmt_second_highest_bid->execute();
                    $second_highest_result = $stmt_second_highest_bid->get_result();

                    if ($second_highest_result->num_rows > 0) {
                        $second_highest_row = $second_highest_result->fetch_assoc();
                        $second_highest_bidder_id = $second_highest_row['buyer_id'];
                        $second_highest_bidder_amount = $second_highest_row['amount'];
                        $second_highest_bidder_id = $second_highest_row['buyer_id'];
                        $second_highest_bidder_email = $second_highest_row['email'];

                        //get item name for email
                        $itemname = mysqli_query($conn, "SELECT itemname FROM auction WHERE auction_id=$auction_id");
                        $itemname_row = mysqli_fetch_assoc($itemname);
                        $itemname = $itemname_row['itemname'];

                        //Send email to second highest bidder
                        $subject_message = 'Your bid has been beaten!';
                        $body_message = 'Your bid on ' . $itemname . ' has been beaten. Click <a href="http://localhost:8888/DBF_CW_Auction/auction/mybids.php">here</a> to react.';
                        $recipient_email = $second_highest_bidder_email;
                        send_email($subject_message, $body_message, $recipient_email);

                        //Send email to current bidder
                        $subject_message = 'Your bid has been placed!';
                        $body_message = 'Your bid on ' . $itemname . ' has been placed. We will notify you if it is beaten, and when the auction has ended.';
                        $recipient_email = $user_email;
                        send_email($subject_message, $body_message, $recipient_email);
                    }
                } else {
                    // Inform the user
                    $message = "You don't have the highest bid, but your bid has been placed.";
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
        } else {
            $error_message = "Error: " . $stmt->error;
        }

        // Close prepared statements
        $stmt->close();
        $stmt_highest_bid->close();

        // Close $stmt_second_highest_bid only if it's not null
        if (isset($stmt_second_highest_bid) && $stmt_second_highest_bid !== null) {
            $stmt_second_highest_bid->close();
        }
    }

    // Close connection
    $conn->close();
}
?>

<html>

<body>
    <?php
    // Display success or error message, if any
    if (isset($message)) {
        echo "<p>$message</p>";
    }
    if (isset($error_message)) {
        echo "<p>$error_message</p>";
    }
    ?>

</body>

</html>