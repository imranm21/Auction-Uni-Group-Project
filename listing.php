<?php 
session_start();
include_once("header.php");
require("utilities.php");

  // Check if the user is logged in
  if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    // Redirect to the login page or handle the case when the user is not logged in
    header("Location: login.php");
    exit();
  }

  // Get the user ID from the session
  $buyer_id = $_SESSION['ID']; // works here but not in watchlist_funcs.php

  // Get info from the URL:
  $item_id = $_GET['item_id'];

  // TODO: Use item_id to make a query to the database.
  $stmt = $conn->prepare("SELECT * FROM auction WHERE auction_id = ?");
  $stmt->bind_param("i", $item_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $auction = $result->fetch_assoc();
    // Now you can access the columns, e.g., $auction['column_name']
  } else {
    echo "No records found";
  }
  $stmt->close();
  // DELETEME: For now, using placeholder data.
  //$title = "Placeholder title";
  $title = $auction["itemname"];
  $description = $auction["ItemDescription"];
  $current_price = $auction["staringprice"];
  $num_bids = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM bid WHERE auction_id=$item_id"));
  $end_time = new DateTime($auction["enddate"]);

  // TODO: Note: Auctions that have ended may pull a different set of data,
  //       like whether the auction ended in a sale or was cancelled due
  //       to lack of high-enough bids. Or maybe not.
  
  // Calculate time to auction end:
  $now = new DateTime();
  
  if ($now < $end_time) {
    $time_to_end = date_diff($now, $end_time);
    $time_remaining = ' (in ' . display_time_remaining($time_to_end) . ')';
  }
  
  // TODO: If the user has a session, use it to make a query to the database
  //       to determine if the user is already watching this item.
  //       For now, this is hardcoded.
  $has_session = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
  $notWatching = isNotWatching($buyer_id, $item_id);
?>


<div class="container">

<div class="row"> <!-- Row #1 with auction title + watch button -->
  <div class="col-sm-8"> <!-- Left col -->
    <h2 class="my-3"><?php echo($title); ?></h2>
  </div>
  <div class="col-sm-4 align-self-center"> <!-- Right col -->
<?php
  /* The following watchlist functionality uses JavaScript, but could
     just as easily use PHP as in other places in the code */
  if ($now < $end_time):
?>
<div id="watch_nowatch" <?php if ($notWatching) echo 'style="display: none"'; ?> >
    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addToWatchlist()">+ Add to watchlist</button>
</div>
<div id="watch_watching" <?php if (!$notWatching) echo 'style="display: none"'; ?> >
    <button type="button" class="btn btn-success btn-sm" disabled>Watching</button>
    <button type="button" class="btn btn-danger btn-sm" onclick="removeFromWatchlist()">Remove watch</button>
</div>

<?php endif /* Print nothing otherwise */ ?>
  </div>
</div>

<div class="row"> <!-- Row #2 with auction description + bidding info -->
  <div class="col-sm-8"> <!-- Left col with item info -->

    <div class="itemDescription">
    <?php echo($description); ?>
    </div>

  </div>

  <div class="col-sm-4"> <!-- Right col with bidding info -->

    <p>
<?php if ($now > $end_time): ?>
     This auction ended <?php echo(date_format($end_time, 'j M H:i')) ?>
     <?php
      $top_bid = get_highest_bid($item_id);
      $stmt = $conn->prepare("SELECT username FROM user WHERE user_id = ?");
      $stmt->bind_param("i", $top_bid[0]);
      $stmt->execute();
      $top_bidder = $stmt->get_result()->fetch_assoc()['username'];
      $stmt->close();
    ?>
    <div class="row">
    <?php if (isset($top_bidder)): ?>
      <?php echo($top_bidder) ?> won this auction with <?php echo($top_bid[1]) ?>
     <!-- TODO: Print the result of the auction here? -->
     <?php else: ?>
      There was no bid for this Auction
    <?php endif ?>
    </div>
<?php else: ?>
     Auction ends <?php echo(date_format($end_time, 'j M H:i') . $time_remaining) ?></p>  

    <!-- Bidding form -->
    <form action="place_bid.php?id=<?php echo $item_id?>"method="POST">
      <div class="input-group">
        <div class="input-group-prepend">
          <span class="input-group-text">£</span>
        </div>
        <input type="number" name="amount" class="form-control">
      <input type="hidden" name="auction_id" value="<?php echo htmlspecialchars($item_id);?>">
      </div>
      <button type="submit" class="btn btn-primary form-control">Place bid</button>
    </form>
<?php endif ?>

  
  </div> <!-- End of right col with bidding info -->

</div> <!-- End of row #2 -->








<script> 
// JavaScript functions: addToWatchlist and removeFromWatchlist.

function addToWatchlist(button) {
  console.log("Adding to watchlist - item_id:", <?php echo $item_id; ?>, "buyer_id:", <?php echo $buyer_id; ?>);
  console.log("These print statements are helpful for debugging btw");
  // This performs an asynchronous call to a PHP function using POST method.
  // Sends item ID as an argument to that function.
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {
      functionname: 'add_to_watchlist',
      arguments: {
        item_id: <?php echo($item_id);?>,
        buyer_id: <?php echo($buyer_id);?>
      }
    },
    success: 
      function (obj, textstatus) {
        // Callback function for when call is successful and returns obj
        console.log("Success");
        var objT = obj.trim();
 
        if (objT == "success") {
          $("#watch_nowatch").hide();
          $("#watch_watching").show();
        }
        else {
          var mydiv = document.getElementById("watch_nowatch");
          mydiv.appendChild(document.createElement("br"));
          mydiv.appendChild(document.createTextNode("Add to watch failed. Try again later."));
        }
      },
    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  }); // End of AJAX call
} // End of addToWatchlist func
function removeFromWatchlist(button) {
  // This performs an asynchronous call to a PHP function using POST method.
  // Sends item ID as an argument to that function.
  $.ajax('watchlist_funcs.php', {
    type: "POST",
    data: {
      functionname: 'remove_from_watchlist',
      arguments: {
        item_id: <?php echo($item_id);?>,
        buyer_id: <?php echo($buyer_id);?> 
      }
    },
    success: 
      function (obj, textstatus) {
        // Callback function for when call is successful and returns obj
        console.log("Success");
        var objT = obj.trim();
 
        if (objT == "success") {
          $("#watch_watching").hide();
          $("#watch_nowatch").show();
        }
        else {
          var mydiv = document.getElementById("watch_watching");
          mydiv.appendChild(document.createElement("br"));
          mydiv.appendChild(document.createTextNode("Watch removal failed. Try again later."));
        }
      },
    error:
      function (obj, textstatus) {
        console.log("Error");
      }
  }); // End of AJAX call
} // End of addToWatchlist func



</script>

<div class="row"> 
  <div class="col-sm-8"> <!-- Left col -->
    <h2 class="my-3">
      <?php 
$bid_list_query = "SELECT * FROM bid WHERE auction_id = $item_id ORDER BY amount DESC";
$bid_list_result = mysqli_query($conn, $bid_list_query);

if ($bid_list_result) {
    if (mysqli_num_rows($bid_list_result) > 0) {
        // Output data for each row
        while ($row = mysqli_fetch_assoc($bid_list_result)) {
            $buyer_id = (int)$row["buyer_id"];
            
            // Use prepared statement to fetch username
            $user_query = mysqli_prepare($conn, "SELECT username FROM user WHERE user_id = ?");
            mysqli_stmt_bind_param($user_query, "i", $buyer_id);
            mysqli_stmt_execute($user_query);
            mysqli_stmt_bind_result($user_query, $user_name);
            mysqli_stmt_fetch($user_query);
            mysqli_stmt_close($user_query);

            $time_stamp = $row["timestamp"];
            $amount = $row["amount"];
            print_listing_bid($buyer_id, $user_name, $time_stamp, $amount);
        }
    } else {
        echo "No bid for the item";
    }
} else {
    echo "Error in the bid list query: " . mysqli_error($conn);
}

    ?>
  </h2>
  </div>
  
<?php include_once("footer.php")?>

