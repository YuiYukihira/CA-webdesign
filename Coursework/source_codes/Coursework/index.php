<?php
session_start();
$error_message = "";

// Connect to database
include("../db_conn.php");

// the password functions were introduced in php5.5 so if we're on a version less than that, add the forwards compat module
if(version_compare(phpversion(), '5.5', '<')) {
	require 'password.php';
}
$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbdatabase);
// If the connection to the database failed kill the process
if($conn->connect_error) {
    die("Connection Failed: " .$conn->connect_error);
}

// Handle a past request for logging in
if(isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];


    $login_stmt = $conn->prepare("SELECT UserID, HashedPW, Permissions from Users WHERE username = ?;");
    $login_stmt->bind_param("s", $username);
    $login_stmt->execute();
    $login_stmt->bind_result($userID, $hashedP, $permissions);
    $login_stmt->fetch();
    $login_stmt->close();
    $conn->close();

    // Check that the given password is equal to the hash
    if(password_verify($password, $hashedP)) {
        $_SESSION["use"] = $userID;
        $_SESSION["userName"] = $username;
        // store our users permissions
        if ($permissions & (1 << 0)) {
            $_SESSION["canSeeStock"] = true;
        } else {
            $_SESSION["canSeeStock"] = false;
        }
        if ($permissions & (1 << 1)) {
            $_SESSION["canSetStock"] = true;
        } else {
            $_SESSION["canSetStock"] = false;
        }
        if ($permissions & (1 << 2)) {
            $_SESSION["canAdmin"] = true;
        } else {
            $_SESSION["canAdmin"] = false;
        }
        // if the user has not been given a token for the forms generate one now.
        if(empty($_SESSION['token'])) {
            $_SESSION['token'] = bin2hex(random_bytes(32));
        }
        // redirect to the stock page
        header("location: stock.php");
    } else {
        // display a login error message
        $error_message = "Invalid Username or Password";
    }
}
?>
<html>
    <head>
        <title>Login Page</title>
        <link rel="stylesheet" type="text/css" href="styles/index.css">
    </head>
    <body>
        <div id="container">
            <div id="login">
                <div id="errorMessage">
<?php
echo $error_message;
?>
                </div>
                <form action="<?php echo htmlentities($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div>Username: <input type="text" name="username" class="textInputs"/></div>
                    <div>Password: <input type="password" name="password" class="textInputs"/></div>
                    <div id="loginButtonDiv"><input type="submit" name="login" value="LOGIN" id="loginButton"></div>
                </form>
            </div>
        </div>
    </body>
</html>
