<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';?>

<?php

// display_time_remaining:
// Helper function to help figure out what time to display
session_start();

$servername = "127.0.0.1";
$username = "root";
$password = "admin123";
$database = "test2";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

function display_time_remaining($interval) {

    if ($interval->days == 0 && $interval->h == 0) {
      // Less than one hour remaining: print mins + seconds:
      $time_remaining = $interval->format('%im %Ss');
    }
    else if ($interval->days == 0) {
      // Less than one day remaining: print hrs + mins:
      $time_remaining = $interval->format('%hh %im');
    }
    else {
      // At least one day remaining: print days + hrs:
      $time_remaining = $interval->format('%ad %hh');
    }

  return $time_remaining;

}

function get_highest_bid($auction_id) {
  $query = 
  "SELECT 
    buyer_id,
    amount,
    timestamp
  FROM bid
  WHERE amount = ( SELECT MAX(amount) FROM bid WHERE auction_id = $auction_id)
  AND auction_id = $auction_id;";

  $result = mysqli_query($GLOBALS['conn'], $query);
  $row = mysqli_fetch_assoc($result);
  return [$row['buyer_id'],$row["amount"]];
}

function send_email($subject_message, $body_message, $recipient_email) {

  //Set up server
  $mail = new PHPMailer(true);
  $mail->SMTPDebug = 0;
  $mail->isSMTP();
  $mail->Host = 'smtp.gmail.com';
  $mail->SMTPAuth = true;
  $mail->Username = 'imranmooraj@gmail.com';
  $mail->Password = 'gphl fsca zpcw ubuz';
  $mail->SMTPSecure = 'tls';
  $mail->Port = 587;

  // Sender details
  $mail->setFrom('imranmooraj@gmail.com');

  // Recipient details
  $mail->addAddress($recipient_email);

  // Email content
  $mail->isHTML(true);
  $mail->Subject = $subject_message;
  $mail->Body    = $body_message;

  // Send email
  $mail->send();
}


function check_me_win($buyer_id) {
  $query = "SELECT 
  a.auction_id, 
  a.ItemDescription, 
  a.category, 
  a.itemname, 
  a.enddate,
  b.amount AS highest_bid_amount
FROM 
  auction a
JOIN 
  bid b ON a.auction_id = b.auction_id
WHERE 
  a.enddate < NOW() -- Ensures the auction has ended
  AND b.amount = (
      SELECT MAX(b2.amount)
      FROM bid b2
      WHERE b2.auction_id = a.auction_id
  ) -- Selects the highest bid for the auction
  AND b.buyer_id = $buyer_id;";
  $result = mysqli_query($GLOBALS['conn'], $query);
  return mysqli_num_rows($result);
}

function check_my_auc_end($seller_id) {
  $query = "SELECT 
  auction_id,
  itemname,
  ItemDescription,
  category,
  startingprice,
  reservedprice,
  enddate
FROM 
  auction
WHERE 
  seller_id = $seller_id AND 
  enddate < NOW();
";
$result = mysqli_query($GLOBALS['conn'], $query);
return mysqli_num_rows($result);
}

function isNotWatching($buyer, $item) {
  $query = "SELECT * FROM wishlist WHERE buyer_id = $buyer AND auction_id = $item";
  $result = mysqli_query($GLOBALS['conn'], $query);
  return !mysqli_num_rows($result) == 0;
}

// print_listing_li:
// This function prints an HTML <li> element containing an auction listing
function print_listing_li($item_id, $title, $desc, $price, $num_bids, $end_time, $watching) {

  // Truncate long descriptions
  if (strlen($desc) > 250) {
    $desc_shortened = substr($desc, 0, 250) . '...';
  }
  else {
    $desc_shortened = $desc;
  }
  // Fix language of bid vs. bids
  if ($num_bids == 1) {
    $bid = ' bid';
  }
  else {
    $bid = ' bids';
  }
  
  // Calculate time to auction end
  $now = new DateTime();
  //$title_class = '';
  if ($now > $end_time) {
    $time_remaining = 'This auction has ended';
    //$title_class = ' style="color: red;"';
  }
  else {
    // Get interval:
    $time_to_end = date_diff($now, $end_time);
    $time_remaining = display_time_remaining($time_to_end) . ' remaining';
  }
  $titleClass = $watching ? 'title-watching' : 'title-not-watching';

  $top_bid = get_highest_bid($item_id)[1];

  // Print HTML
  echo('
    <li class="list-group-item d-flex justify-content-between">
    <div class="p-2 mr-5">
        <h5><a class="' . $titleClass . '" href="listing.php?item_id=' . $item_id . '">' . $title . '</a></h5>
        ' . $desc_shortened . '
    </div>
    <div class="text-center text-nowrap"><span style="font-size: 1.5em">£' . number_format($top_bid, 2) . '</span><br/>' . $num_bids . $bid . '<br/>' . $time_remaining . '</div>
  </li>'
  );

}

function print_listing_bid($buyer_id, $user_name,$time_stamp, $amount)
{
  
    // Print HTML
    echo('
    <li class="list-group-item d-flex justify-content-between">
        <div class="p-2 mr-5">
            <h5 style="font-size: 0.9em">Buyer: ' . $user_name . '</a></h5></div>
        <div class="text-center text-nowrap">
            <span style="font-size: 1.0em">£' . number_format($amount,2) . '</span><br/>' . $time_stamp . '<br/></div>
    </li>'
    );
}




?>