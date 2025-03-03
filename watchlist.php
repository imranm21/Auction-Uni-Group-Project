<?php include_once("header.php") ?>
<?php require("utilities.php") ?>
<div class="container">
  <h2 class="my-3">My Watchlist</h2>
  <div class="container">
    <?php
    // This page is for showing a user their wishlist
    session_start();

    // Check if the user is logged in
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
      // Redirect to the login page or handle the case when the user is not logged in
      header("Location: login.php");
      exit();
    }

    // Get the user ID from the session
    $user_id = $_SESSION['ID'];

    // Create connection
    $conn = mysqli_connect($servername, $username, $password, $database);
    $sql = "SELECT a.* FROM auction a
  INNER JOIN wishlist w ON a.auction_id = w.auction_id
  WHERE w.buyer_id = $user_id";

    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
      // Output data for each row
      while ($row = mysqli_fetch_assoc($result)) {
        $item_id = (int)$row["auction_id"];
        $bid_count = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM bid WHERE auction_id=$item_id"));
        $item_name = $row["itemname"];
        $item_dec = $row["ItemDescription"];
        $item_bid = $row["startingprice"];
        $date = $row["enddate"];
        $item_num = $bid_count;
        $item_end = new DateTime($date);
        
        $watchlistQuery = $conn->prepare("SELECT * FROM wishlist WHERE buyer_id = ? AND auction_id = ?");
        $watchlistQuery->bind_param("ii", $_SESSION['ID'], $item_id);
        $watchlistQuery->execute();
        $watchlistResult = $watchlistQuery->get_result();
        $watching = $watchlistResult->num_rows > 0;

        print_listing_li($item_id, $item_name, $item_dec, $item_bid, $item_num, $item_end, $watching);

        $watchlistQuery->close();
      }
    } else {
      echo "0 results";
    }

    ?>
  </div>
  <?php include_once("footer.php") ?>