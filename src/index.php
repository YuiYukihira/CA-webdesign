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

// Connect to database
include("../db_conn.php");
$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbdatabase);

if($conn->connect_error) {
    die("Connectian Failed: " .$conn->connect_error);
}

if(isset($_POST["login"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $login_stmt = $conn->prepare("SELECT UserID, Name, HashedPW, Permissions from Users WHERE username = ?;");
    $login_stmt->bind_param("s", $username);
    $login_stmt->execute();
    $login_stmt->bind_result($userID, $userName, $hashedP, $permissions);
    $login_stmt->fetch();
    $login_stmt->close();
    $conn->close();

    if(password_verify($password, $hashedP)) {
        session_start();
        $_SESSION["use"] = $userID;
        $_SESSION["userName"] = $userName;
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
        header("location: stock.php");
    } else {
        echo "Invalid Username or Password";
    }
}
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
