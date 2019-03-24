<?php
session_start();
if(!isset($_SESSION["use"])) {
    header("location: index.php");
}
if (!$_SESSION["canAdmin"]) {
    header("lecation: stock.php");
}
if(empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['token'];

$userID = $_SESSION["use"];
$userName = $_SESSION["userName"];

include("../db_conn.php");
$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbdatabase);

if($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

if(isset($_POST["cuSubmit"])) {
    if($_POST["token"] == $token) {
        $cuName = $_POST["cuName"];
        $cuName2 = $_POST["cuName2"];
        $cuPassword = password_hash($_POST["cuPassword"], PASSWORD_BCRYPT);
        $cuPerms = $_POST["cuPerms"];

        $add_stmt = $conn->prepare("INSERT INTO Users (username, Name, HashedPW, Permissions) VALUES (?, ?, ?, ?)");
        $add_stmt->bind_param("sssi", $cuName, $cuName2, $cuPassword, $cuPerms);

        $add_stmt->execute();
        $add_stmt->close();
    }
}

if(isset($_POST["userChange"])) {
    if($_POST["token"] == $token) {
        $userID = $_POST["userID"];
        $userName = $_POST["userName"];
        $userName2 = $_POST["userName2"];
        $userPass = password_hash($_POST["userPassword"], PASSWORD_BCRYPT);
        $userPerms = $_POST["userPerms"];

        if($_POST["userCtype"] == 2) {
            $del_stmt = $conn->prepare("DELETE FROM Users WHERE UserID = ?");
            $del_stmt->bind_param("i", $userID);
            $del_stmt->execute();
            $del_stmt->close();
        }
        if($_POST["userCtype"] == 1) {
            $query = "UPDATE Users SET ";
            $paramTypes = "";
            $params = array();

            $anyupdate = false;
            if($userName != "") {
                $query .= "username = ? ";
                $paramTypes .= "s";
                $params[] = &$userName;
                $anyupdate = true;
            }
            if($userName2 != "") {
                if($anyupdate) {
                    $query .= ", ";
                }
                $query .= "Name = ? ";
                $paramTypes .= "s";
                $params[] = &$userName2;
                $anyupdate = true;
            }
            if($userPass != "") {
                if($anyupdate) {
                    $query .= ", ";
                }
                $query .= "HashedPW = ? ";
                $paramTypes .= "s";
                $params[] = &$userPass;
                $anyupdate = true;
            }
            if($userPerms != "") {
                if($anyupdate) {
                    $query .= ", ";
                }
                $query .= "Permissions = ? ";
                $paramTypes .= "i";
                $params[] = &$userPerms;
                $anyupdate = true;
            }
            if($anyupdate){
                $query .= "WHERE UserID = ?";
                $paramTypes .= "i";
                $params[] = &$userID;
                $update_stmt = $conn->prepare($query);
                call_user_func_array(array($update_stmt, "bind_param"), array_merge(array($paramTypes), $params));
                $update_stmt->execute();
                $update_stmt->close();
            }
        }
    }
}
?>
<html>
    <head>
        <title>Admin Panel</title>
        <link rel="stylesheet" type="text/css" href="styles/admin.css"/>
        <script src="js/sidebar.js"></script>
    </head>
    <body>
        <div id="sidebar">
            <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
            <h1 id="welcometext"><?php echo "Welcome " . $userName . "!"; ?></h1>
            <?php
            if($_SESSION["canAdmin"]) {
                echo "<a href='admin.php'>Admin Panel</a>";
            }
            ?>
            <a href="stock.php">Stock Information</a>
            <a href="logout.php">Log out!</a>
        </div>
        <div style="height: 20px;"></div>
        <span onclick="openNav()" id="openmenu">Menu</span>
        <div id="main">
            <div id="userTable">
                <form style="margin: 0;" action="<?php echo htmlentities($_SERVER["PHP_SELF"]); ?>" method="post">
                    <input type="hidden" name="token" value="<?php echo $token; ?>">
                    <table>
                        <tr>
                            <th>User ID</th>
                            <th>UserName</th>
                            <th>Name</th>
                            <th>Permissions</th>
                            <th>Password</th>
                            <th>Action</th>
                            <th>Submit</th>
                        </tr>
                        <tr>
                            <td>Auto-Generated</td>
                            <td><input type="text" name="cuName"></td>
                            <td><input type="text" name="cuName2"></td>
                            <td><input type="number" min="0" max="7" name="cuPerms"></td>
                            <td><input type="password" name="cuPassword"></td>
                            <td></td>
                            <td><input type="submit" value="create new user" name="cuSubmit"></td>
                        </tr>
                    </table>
                </form>
                    <?php
                    $user_stmt = $conn->prepare("SELECT UserID, username, Name, Permissions FROM Users");
                    $user_stmt->bind_result($uid, $uname, $uname2, $uperms);
                    $user_stmt->execute();
                    while($user_stmt->fetch()) {
                        echo "<form style='margin: 0;' action='" . htmlentities($_SERVER["PHP_SELF"]) . "' method='post'><input type='hidden' name='token' value='" . $token . "'><table><tr>";
                        echo "<td><input type='hidden' name='userID' value='" . $uid . "'>" . $uid . "</td>";
                        echo "<td><input type='text' value='" . $uname . "' name='userName'></td>";
                        echo "<td><input type='text' value='" . $uname2 . "' name='userName2'></td>";
                        echo "<td><input type='number' min='0' max='7' value='" . $uperms . "' name='userPerms'></td>";
                        echo "<td><input type='text' name='userPassword'></td>";
                        echo "<td><select name='userCtype'><option value='1' selected >Change</option><option value='2'>Remove</option></select></td>";
                        echo "<td><input type='submit' value='GO!' name='userChange'></td>";
                        echo "</tr></table></form>";
                    }
                    $user_stmt->close();
                    $conn->close();
                    ?>
            </div>
        </div>
    </body>
</html>
