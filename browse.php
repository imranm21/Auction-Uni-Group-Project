<?php include_once("header.php")?>
<?php require("utilities.php")?>

<div class="container">

<h2 class="my-3">Browse listings</h2>

<div id="searchSpecs">
<!-- When this form is submitted, this PHP page is what processes it.
     Search/sort specs are passed to this page through parameters in the URL
     (GET method of passing data to a page). -->
<form method="get" action="search.php">
  <div class="row">
    <div class="col-md-5 pr-0">
      <div class="form-group">
        <label for="keyword" class="sr-only">Search keyword:</label>
	    <div class="input-group">
          <div class="input-group-prepend">
            <span class="input-group-text bg-transparent pr-0 text-muted">
              <i class="fa fa-search"></i>
            </span>
          </div>
          <input type="text" class="form-control border-left-0" id="keyword" name="keyword" placeholder="Search for anything">
        </div>
      </div>
    </div>
    <div class="col-md-3 pr-0">
      <div class="form-group">
        <label for="cat" class="sr-only">Search within:</label>
        <select class="form-control" id="cat" name="cat">
          <option selected value="ALL">All categories</option>
          <?php
            // Fetch distinct categories from the database
            $all_categories_query = "SELECT DISTINCT category FROM auction";
            $result = mysqli_query($conn, $all_categories_query);
            
            // Check if the query was successful
            if ($result) {
              // Iterate over the result set and create options
              while ($row = mysqli_fetch_assoc($result)) {
                $category = $row['category'];
                echo "<option value='$category'>$category</option>";
              }
              // Free the result set
              mysqli_free_result($result);
            } else {
              // Handle the case when the query fails
              echo "Error: " . mysqli_error($conn);
            }
            ?>
            
        </select>
      </div>
    </div>
    <div class="col-md-3 pr-0">
      <div class="form-inline">
        <label class="mx-2" for="order_by">Sort by:</label>
        <select class="form-control" id="order_by" name="order_by">
          <option selected value="ASC">Price (low to high)</option>
          <option value="DESC">Price (high to low)</option>
          <option value="enddate ASC">Soonest expiry</option>
        </select>
      </div>
    </div>
    <div class="col-md-1 px-0">
      <button type="submit" class="btn btn-primary">Search</button>
    </div>
  </div>
</form>
</div> <!-- end search specs bar -->


</div>

<?php
  // Retrieve these from the URL
  if (!isset($_GET['keyword'])) {
    // TODO: Define behavior if a keyword has not been specified.
  }
  else {
    $keyword = $_GET['keyword'];
  }

  if (!isset($_GET['cat'])) {
    // TODO: Define behavior if a category has not been specified.
  }
  else {
    $category = $_GET['cat'];
  }
  
  if (!isset($_GET['order_by'])) {
    // TODO: Define behavior if an order_by value has not been specified.
  }
  else {
    $ordering = $_GET['order_by'];
  }
  
  if (!isset($_GET['page'])) {
    $curr_page = 1;
  }
  else {
    $curr_page = $_GET['page'];
  }

  /* TODO: Use above values to construct a query. Use this query to 
     retrieve data from the database. (If there is no form data entered,
     decide on appropriate default value/default query to make. */
  //$sql = "SELECT * FROM auction LIMIT " . $initial_page . ',' . $results_per_page;
  $initial_page = $curr_page - 1;
  //$sql = "SELECT * FROM auction LIMIT" . $initial_page . ',' . 10;
  /* For the purposes of pagination, it would also be helpful to know the
     total number of results that satisfy the above query */
  $num_results = (int)mysqli_num_rows(mysqli_query($conn, "SELECT * FROM auction"));
  $results_per_page = 15;
  $max_page = ceil($num_results / $results_per_page);
?>


<div class="container mt-5">

<!-- TODO: If result set is empty, print an informative message. Otherwise... -->

<ul class="list-group">
<?php
  //$initial_page = $curr_page - 1;
  $offset = $initial_page * $results_per_page;
  $result = mysqli_query($conn, "SELECT * FROM auction LIMIT $results_per_page OFFSET $offset");

  if (!isset($_SESSION['username'])) {
    echo "Please login to view this page.";
  } elseif (mysqli_num_rows($result) > 0) {
      // Output data for each row
    while($row = mysqli_fetch_assoc($result)) {
        //echo $row["username"]. $row["email"]. "<br>";
        $item_id = (int)$row["auction_id"];
        $bid_count = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM bid WHERE auction_id=$item_id"));
        $item_name = $row["itemname"];
        $item_dec = $row["ItemDescription"];
        $item_bid = $row["startingprice"];
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
<!-- TODO: Use a while loop to print a list item for each auction listing
     retrieved from the query -->
    
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
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page - 1) . '" aria-label="Previous">
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
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . $i . '">' . $i . '</a>
    </li>');
  }
  
  if ($curr_page != $max_page) {
    echo('
    <li class="page-item">
      <a class="page-link" href="browse.php?' . $querystring . 'page=' . ($curr_page + 1) . '" aria-label="Next">
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

<?php if (check_my_auc_end($_SESSION['ID']) > 0 && !isset($_SESSION['notif'])): ?>
  <script>
document.addEventListener('DOMContentLoaded', (event) => {
    document.getElementById('auctionNotif').style.display = 'block';
    var modal = new bootstrap.Modal(document.getElementById('auctionNotif'));
    modal.show();
});
</script>;
<?php $_SESSION['notif'] = true; ?>
<?php endif ?>
</div>



<?php include_once("footer.php")?>
