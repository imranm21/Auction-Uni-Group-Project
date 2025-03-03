<?php require("utilities.php")?>
<?php
// Assuming $conn is the existing mysqli connection

// Function to get wishlists from the database
function getWishlists() {
    global $conn;
    $result = $conn->query("SELECT buyer_id, auction_id FROM wishlist");
    $wishlists = [];

    while ($row = $result->fetch_assoc()) {
        $wishlists[$row['buyer_id']][] = $row['auction_id'];
    }

    return $wishlists;
}

// Function to calculate similarity between two wishlists
function calculateSimilarity($wishlist1, $wishlist2) {
    $commonItems = array_intersect($wishlist1, $wishlist2);
    return count($commonItems);
}

// Function to find users with similar wishlists
function findSimilarUsers($currentUser, $wishlists) {
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
function recommendItems($currentUser, $wishlists) {
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

// Fetch wishlists
$wishlists = getWishlists();

// Assume current user ID
$currentUserId = 17; // Example user ID

// Get recommendations
$recommendations = recommendItems($currentUserId, $wishlists);

// Output the recommendations
print_r($recommendations);
?>
