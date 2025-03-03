<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Yee-Bay</title>

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/index.css" rel="stylesheet">

</head>

<body>
    <?php if (isset($_GET['message'])): ?>
        <div class="alert alert-warning">
        <?php echo $_GET['message']; ?>
        </div>
    <?php endif; ?>


	<!-- Header -->
    <header>
        <div class="header-content">
            <div class="header-content-inner">
                <h1>Yee-Bay</h1>
                <p style="color:white; background-color: rgba(0, 0, 0, 0.5);"><strong>Don't hesitate on your true desire</strong></p>
                <a href="browse.php" class="btn btn-primary btn-lg">Bid now</a>
            </div>
        </div>
    </header>
</body>
</html>
