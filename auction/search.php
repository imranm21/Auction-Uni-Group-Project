<?php include_once("header.php")?>
<?php require("utilities.php")?>

<div class="container mt-5">

<h2 class="my-3">Search</h2>

<ul class="list-group">



<?php
  session_start();
  if (!isset($_GET['page'])) {
    $curr_page = 1;
  }
  else {
    $curr_page = $_GET['page'];
  }
  $initial_page = $curr_page - 1;
  //$sql = "SELECT * FROM auction LIMIT" . $initial_page . ',' . 10;
  /* For the purposes of pagination, it would also be helpful to know the
     total number of results that satisfy the above query */
  $results_per_page = 15;
?>

<div class="container mt-5">

<!-- TODO: If result set is empty, print an informative message. Otherwise... -->

<ul class="list-group">
<?php
  //$initial_page = $curr_page - 1;
  $offset = $initial_page * $results_per_page;
  // Get info from the URL:
  $category = $_GET['cat'];
  $ordering = $_GET['order_by'];
  $keyword = $_GET['keyword'];

  if ($ordering == "enddate ASC"){
    if ($keyword == "") {
      // No keyword provided
      if ($category == "ALL") {
        $num_results = (int)mysqli_num_rows(mysqli_query($conn, "SELECT * FROM auction"));
        $max_page = ceil($num_results / $results_per_page);
          $result = mysqli_query($conn, "SELECT * FROM auction ORDER BY $ordering LIMIT $results_per_page OFFSET $offset");
      } else {
          $num_results = (int)mysqli_num_rows(mysqli_query($conn, "SELECT * FROM auction where category = '$category'"));
          $max_page = ceil($num_results / $results_per_page);
          $result = mysqli_query($conn, "SELECT * FROM auction WHERE category = '$category' ORDER BY $ordering LIMIT $results_per_page OFFSET $offset");
      }
    } else {
      if ($category == "ALL"){
        $num_results = (int)mysqli_num_rows(mysqli_query($conn, "SELECT * FROM auction WHERE itemname like '%$keyword%' "));
        $max_page = ceil($num_results / $results_per_page);
      // Keyword is set, perform search
      $search = "SELECT * FROM auction WHERE itemname like '%$keyword%' order by $ordering LIMIT $results_per_page OFFSET $offset";
      $result = mysqli_query($conn, $search);}
      else{
        $num_results = (int)mysqli_num_rows(mysqli_query($conn, "SELECT * FROM auction WHERE itemname like '%$keyword%' and category = '$category'"));
        $max_page = ceil($num_results / $results_per_page);
        $search = "SELECT * FROM auction WHERE itemname like '%$keyword%' and category = '$category' order by $ordering LIMIT $results_per_page OFFSET $offset";
        $result = mysqli_query($conn, $search);
      }
  }
  }else{
    if ($keyword == "") {
      // No keyword provided
      if ($category == "ALL") {
        $num_results = (int)mysqli_num_rows(mysqli_query($conn, "SELECT * FROM auction"));
        $max_page = ceil($num_results / $results_per_page);
          $result = mysqli_query($conn, "SELECT 
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
          b.amount = (
              SELECT MAX(b2.amount)
              FROM bid b2
              WHERE b2.auction_id = a.auction_id
          ) ORDER BY amount $ordering LIMIT $results_per_page OFFSET $offset");
      } else {
        $num_results = (int)mysqli_num_rows(mysqli_query($conn, "SELECT * FROM auction where category = '$category'"));
        $max_page = ceil($num_results / $results_per_page);
          $result = mysqli_query($conn, "SELECT 
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
          b.amount = (
              SELECT MAX(b2.amount)
              FROM bid b2
              WHERE b2.auction_id = a.auction_id
          ) AND category = '$category' ORDER BY amount $ordering LIMIT $results_per_page OFFSET $offset");
      }
    } else {
      if ($category == "ALL"){
        $num_results = (int)mysqli_num_rows(mysqli_query($conn, "SELECT * FROM auction WHERE itemname like '%$keyword%' "));
        $max_page = ceil($num_results / $results_per_page);
      // Keyword is set, perform search
      $search = "SELECT 
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
      b.amount = (
          SELECT MAX(b2.amount)
          FROM bid b2
          WHERE b2.auction_id = a.auction_id
      ) and itemname like '%$keyword%' order by amount $ordering LIMIT $results_per_page OFFSET $offset";
      $result = mysqli_query($conn, $search);}
      else{
        $num_results = (int)mysqli_num_rows(mysqli_query($conn, "SELECT * FROM auction WHERE itemname like '%$keyword%' and category = '$category'"));
        $max_page = ceil($num_results / $results_per_page);
        $search = "SELECT 
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
        b.amount = (
            SELECT MAX(b2.amount)
            FROM bid b2
            WHERE b2.auction_id = a.auction_id
        ) and itemname like '%$keyword%' and category = '$category' order by amount $ordering LIMIT $results_per_page OFFSET $offset";
        $result = mysqli_query($conn, $search);
  
      }
  }
  }
  

  

  if (mysqli_num_rows($result) > 0) {
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
    }
  } else {
      echo "0 results";
  }
?>

<?php
$now = new DateTime();
echo $now->format('Y-m-d H:i:s');
?>

</ul>

<!-- Pagination for results listings -->
<nav aria-label="Search results pages" class="mt-5">
  <ul class="pagination justify-content-center">
  
<?php

  // Copy any currently-set GET variables to the URL.
  $querystring = "";
  foreach ($_GET as $key => $value) {
    if ($key != "page") {
      $querystring .= "$key=$value&amp;";
    }
  }
  
  $high_page_boost = max(3 - $curr_page, 0);
  $low_page_boost = max(2 - ($max_page - $curr_page), 0);
  $low_page = max(1, $curr_page - 2 - $low_page_boost);
  $high_page = min($max_page, $curr_page + 2 + $high_page_boost);
  
  if ($curr_page != 1) {
    echo('
    <li class="page-item">
      <a class="page-link" href="search.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
        <span aria-hidden="true"><i class="fa fa-arrow-left"></i></span>
        <span class="sr-only">Previous</span>
      </a>
    </li>');
  }
    
  for ($i = $low_page; $i <= $high_page; $i++) {
    if ($i == $curr_page) {
      // Highlight the link
      echo('
    <li class="page-item active">');
    }
    else {
      // Non-highlighted link
      echo('
    <li class="page-item">');
    }
    
    // Do this in any case
    echo('
      <a class="page-link" href="search.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
    </li>');
  }
  
  if ($curr_page != $max_page) {
    echo('
    <li class="page-item">
      <a class="page-link" href="search.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
        <span aria-hidden="true"><i class="fa fa-arrow-right"></i></span>
        <span class="sr-only">Next</span>
      </a>
    </li>');
  }
?>

  </ul>
</nav>
<?php if (check_me_win($_SESSION['ID']) > 0 && !isset($_SESSION['notif'])): ?>
  <script>
document.addEventListener('DOMContentLoaded', (event) => {
    document.getElementById('notificationModal').style.display = 'block';
    var modal = new bootstrap.Modal(document.getElementById('notificationModal'));
    modal.show();
});
</script>;
<?php $_SESSION['notif'] = true; ?>
<?php endif ?>


</div>



<?php include_once("footer.php")?>
