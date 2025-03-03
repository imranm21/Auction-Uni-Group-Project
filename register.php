<?php
session_start(); // Start the session at the beginning of the file

// Check if there are any session-stored error messages or form data
$errorMessages = isset($_SESSION['error_messages']) ? $_SESSION['error_messages'] : [];
$sql_errorMessages = isset($_SESSION['sql_error_messages']) ? $_SESSION['sql_error_messages'] : [];
$formData = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : [];

// Clear the error messages from the session after displaying them
unset($_SESSION['error_messages']);
unset($_SESSION['sql_error_messages']);
unset($_SESSION['form_data']);
?>

<?php include_once("header.php"); ?>
<div class="container">
    <h2 class="my-3">Register new account</h2>

    <!-- Error Messages Display -->
    <?php if (!empty($errorMessages)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errorMessages as $message): ?>
            <p><?php echo $message; ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($sql_errorMessages)): ?>
    <div class="alert alert-danger">
        <p><?php echo $sql_errorMessages; ?></p>
    </div>
    <?php endif; ?>

    <!-- Registration Form -->
    <form method="POST" action="process_registration.php">
        <!-- Rest of the form fields will remain unchanged. Just ensure the 'value' attribute is set for each input field -->
        <!-- Email Field -->
          <div class="form-group row">
    <label for="accountType" class="col-sm-2 col-form-label text-right">Registering as a:</label>
	<div class="col-sm-10">
	  <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="accountType" id="accountBuyer" value="buyer" checked>
        <label class="form-check-label" for="accountBuyer">Buyer</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="accountType" id="accountSeller" value="seller">
        <label class="form-check-label" for="accountSeller">Seller</label>
      </div>
      <small id="accountTypeHelp" class="form-text-inline text-muted"><span class="text-danger">* Required.</span></small>
	</div>
  </div>
        <div class="form-group row">
            <label for="email" class="col-sm-2 col-form-label text-right">Email</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($formData['email'] ?? ''); ?>" required>
                <small id="emailHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
            </div>
        </div>

        <!-- Username Field -->
        <div class="form-group row">
            <label for="username" class="col-sm-2 col-form-label text-right">Username</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="<?php echo htmlspecialchars($formData['username'] ?? ''); ?>" required>
                <small id="usernameHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
            </div>
        </div>

        <!-- Password Field -->
        <div class="form-group row">
            <label for="password" class="col-sm-2 col-form-label text-right">Password</label>
            <div class="col-sm-10">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <small id="passwordHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
            </div>
        </div>

        <!-- Password Confirmation Field -->
        <div class="form-group row">
            <label for="passwordConfirmation" class="col-sm-2 col-form-label text-right">Repeat password</label>
            <div class="col-sm-10">
                <input type="password" class="form-control" id="passwordConfirmation" name="passwordConfirmation" placeholder="Enter password again" required>
                <small id="passwordConfirmationHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="form-group row">
            <button type="submit" class="btn btn-primary form-control">Register</button>
        </div>
    </form>

    <!-- Login Link -->
    <div class="text-center">
        Already have an account? <a href="" data-toggle="modal" data-target="#loginModal">Login</a>
    </div>
</div>

<?php include_once("footer.php"); ?>
