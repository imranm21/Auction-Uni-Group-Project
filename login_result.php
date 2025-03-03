<?php require("utilities.php")?>

<?php

// TODO: Extract $_POST variables, check they're OK, and attempt to login.
// Notify user of success/failure and redirect/give navigation options.

// For now, I will just set session variables and redirect.

session_start();

// Extract $_POST variables
$email = $_POST['email'];
$password = $_POST['password'];
$result = mysqli_query($conn, "SELECT * FROM user WHERE email = '$email'") or die(mysqli_error($conn));

if ($result->num_rows != 0) {
    // Output data of the row
    $row = $result->fetch_assoc();
    $hashed_password = $row["password"];
    if (password_verify($password, $hashed_password)) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $row["username"];
        $_SESSION['account_type'] = strtolower($row["status"]);
        $_SESSION["ID"] = $row["user_id"];
    } else {
        echo 'Invalid password.';
        header("refresh:3;url=index.php");
        exit();
    }

    echo('<div class="text-center">You are now logged in! You will be redirected shortly.</div>');
    // Redirect to index after 5 seconds
    header("refresh:3;url=index.php");
    exit();

} else {
    $_SESSION['logged_in'] = false;
    unset($_SESSION['username']);
    unset($_SESSION['account_type']);
    echo('<div class="text-center">Wrong credentials provided, please try again.</div>');
    header("refresh:3;url=index.php");
    exit();
}

?>
