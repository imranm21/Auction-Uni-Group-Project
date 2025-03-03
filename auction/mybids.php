<?php include_once("header.php")?>
<?php require("utilities.php")?>

<div class="container mt-5">

<h2 class="my-3">My bids</h2>

<ul class="list-group">
<?php
  session_start();
  // This page is for showing a user the auctions they've bid on.
  // It will be pretty similar to browse.php, except there is no search bar.
  // This can be started after browse.php is working with a database.
  // Feel free to extract out useful functions from browse.php and put them in
  // the shared "utilities.php" where they can be shared by multiple files.
  
  
  // TODO: Check user's credentials (cookie/session).
  
  // TODO: Perform a query to pull up the auctions they've bidded on.
  
  // TODO: Loop through results and print them out as list items.
  
  $result = mysqli_query($conn, "SELECT * FROM auction 
  WHERE auction_id IN (
      SELECT auction_id FROM bid WHERE buyer_id ='" . $_SESSION['ID'] . "')");

  if (!isset($_SESSION['username'])) {
    echo "Please login to view this page.";
    header("refresh:3;url=index.php");
  } elseif (mysqli_num_rows($result) > 0) {
      // Output data for each row
      while($row = mysqli_fetch_assoc($result)) {
          //echo $row["username"]. $row["email"]. "<br>";
          
          $item_id = (int)$row["auction_id"];
          $bid_count = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM bid WHERE auction_id=$item_id"));
          $item_name = $row["itemname"];
          $item_dec = $row["ItemDescription"];
          $item_bid = $row["startingprice"];
          //$item_end = $row["enddate"];
          $date = $row["enddate"];
          $item_end = new DateTime($date);
          $item_num = $bid_count;
          
          // check if in watchlist
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

<?php include_once("footer.php")?>