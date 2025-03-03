<?php require("utilities.php")?>
<?php
session_start(); // Start the session


// Function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to validate username
function isValidUsername($username) {
    return preg_match('/^[A-Za-z0-9_]+$/', $username);
}

// Initialize an array to store error messages
$errorMessages = [];

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accountType = $_POST['accountType'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $passwordConfirmation = $_POST['passwordConfirmation'];

    // Validate email
    if (empty($email) || !isValidEmail($email)) {
        $errorMessages[] = "Please enter a valid email address.";
    }

    // Validate username
    if (empty($username) || !isValidUsername($username)) {
        $errorMessages[] = "Username can only contain letters, numbers, and underscores.";
    }

    // Check for password match
    if ($password !== $passwordConfirmation) {
        $errorMessages[] = "Passwords do not match.";
    }

    // If there are errors, redirect back to the form with errors and form data
    if (!empty($errorMessages)) {
        $_SESSION['error_messages'] = $errorMessages;
        $_SESSION['form_data'] = $_POST;
        header('Location: register.php');
        exit();
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        // Redirect to a new page or display a success message
        $insert_stmt = "INSERT INTO user (status, email, username, password) VALUES ('$accountType', '$email', '$username', '$hashed_password')";
        
        // Connect to the database

        // Check connection
        if (!$conn) {
            $error = mysqli_connect_error();
            echo "Failed to connect to MySQL: " . $error;
            exit();
        }

        if ($conn->query($insert_stmt) === TRUE) {
            echo "New record created successfully";
            header("refresh:3;url=index.php");
            exit();
          } else {
            //echo "Error: " . $sql . "<br>" . $conn->error;
            $error_msg = $conn->error;
            $_SESSION['sql_error_messages'] = $error_msg;
            $_SESSION['form_data'] = $_POST;
            header('Location: register.php');
            exit();
        }
    }
}
?>
