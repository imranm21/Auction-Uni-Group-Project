<?php include_once("header.php") ?>
<?php require("utilities.php") ?>

<div class="container">

  <h2 class="my-3">My listings</h2>
  <div class="container">
    <?php
    session_start();

    // Check if the user is logged in
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
      // Redirect to the login page or handle the case when the user is not logged in
      header("Location: login.php");
      exit();
    }

    // Get the user ID from the session
    $user_id = $_SESSION['ID'];

    // This page is for showing a user the auction listings they've made.
    // It will be pretty similar to browse.php, except there is no search bar.
    // This can be started after browse.php is working with a database.
    // Feel free to extract out useful functions from browse.php and put them in
    // the shared "utilities.php" where they can be shared by multiple files.


    /*   $servername = "127.0.0.1";
  $username = "root";
  $password = "admin123";
  $database = "test2";

  // Create connection
  $conn = mysqli_connect($servername, $username, $password, $database); */
  
    $sql = "SELECT * FROM auction WHERE seller_id =$user_id";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
      // Output data for each row
      while ($row = mysqli_fetch_assoc($result)) {
        //echo $row["username"]. $row["email"]. "<br>";

        $item_id = (int)$row["auction_id"];
        $bid_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM bid WHERE auction_id=$item_id"));
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
      }
    } else {
      echo "0 results";
    }




    // TODO: Check user's credentials (cookie/session).

    // TODO: Perform a query to pull up their auctions.

    // TODO: Loop through results and print them out as list items.

    ?>
  </div>
  <?php include_once("footer.php") ?>