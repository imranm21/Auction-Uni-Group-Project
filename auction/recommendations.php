<?php require("utilities.php") ?>
<?php include_once("header.php") ?>

<?php
// Assuming $conn is the existing mysqli connection

// Function to get wishlists from the database
function getWishlists()
{
    global $conn;
    $result = $conn->query("SELECT buyer_id, auction_id FROM wishlist");
    $wishlists = [];

    while ($row = $result->fetch_assoc()) {
        $wishlists[$row['buyer_id']][] = $row['auction_id'];
    }

    return $wishlists;
}

// Function to calculate similarity between two wishlists
function calculateSimilarity($wishlist1, $wishlist2)
{
    $commonItems = array_intersect($wishlist1, $wishlist2);
    return count($commonItems);
}

// Function to find users with similar wishlists
function findSimilarUsers($currentUser, $wishlists)
{
    $similarities = [];
    foreach ($wishlists as $user => $wishlist) {
        if ($user != $currentUser) {
            $similarities[$user] = calculateSimilarity($wishlists[$currentUser], $wishlist);
        }
    }
    arsort($similarities); // Sort by similarity, highest first
    return array_keys($similarities); // Return sorted users
}

// Function to recommend items
function recommendItems($currentUser, $wishlists)
{
    $similarUsers = findSimilarUsers($currentUser, $wishlists);
    $recommendations = [];

    foreach ($similarUsers as $user) {
        foreach ($wishlists[$user] as $item) {
            if (!in_array($item, $wishlists[$currentUser])) {
                $recommendations[$item] = ($recommendations[$item] ?? 0) + 1;
            }
        }
    }

    arsort($recommendations); // Sort by frequency
    return array_keys($recommendations); // Return recommended items
}
?>
<div class="container mt-5">
    <h2 class="my-3">We thought you might be interested in these items</h2>
    <ul class="list-group">
        <?php
        // Fetch wishlists
        $wishlists = getWishlists();

        // Assume current user ID
        $currentUserId = $_SESSION['ID']; // Example user ID

        // Get recommendations
        $recommendations = recommendItems($currentUserId, $wishlists);

        // Output the recommendations
        $idString = implode(',', array_slice($recommendations, 0, 10));

        // Prepare the SQL query
        $query = "SELECT * FROM auction WHERE auction_id IN ($idString) LIMIT 10";

        // Execute the query
        $rec_items = mysqli_query($conn, $query);

        // Check for errors
        if (!$rec_items) {
            die("Error: " . mysqli_error($conn));
        }

        if (!isset($_SESSION['username'])) {
            echo "Please login to view this page.";
        } elseif (mysqli_num_rows($rec_items) > 0) {
            // Output data for each row
            while ($row = mysqli_fetch_assoc($rec_items)) {
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

                $watchlistQuery->close();
            }
        } else {
            echo "0 results";
        }
        ?>
    </ul>
</div>