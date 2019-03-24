<?php
session_start();
if(isset($_SESSION["use"])) {
    $userID = $_SESSION["use"];
    $userName = $_SESSION["userName"];
} else {
    header("location: index.php");
}
if(empty($_SESSION["token"])) {
    $_SESSION["token"] = bin2hex(random_bytes(32));
}
$token = $_SESSION["token"];

include('../db_conn.php');
$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbdatabase);

if($conn->connect_error) {
    die("Connection Failed: " .$conn->connection_error);
}
if(isset($_POST["addStock"])) {
    if($_POST["token"] == $token && $_SESSION["canSetStock"]) {
        $newStockName = $_POST["addStockName"];
        $newStockPrice = floor($_POST["addStockPrice"]*100);

        $add_stmt = $conn->prepare("INSERT INTO StockInfo(StockName, StockPrice, LastModifiedBy, UserID) VALUES (?, ?, ?, ?);");
        $add_stmt->bind_param("siii", $newStockName, $newStockPrice, $userID, $userID);
        $add_stmt->execute();

        $add_stmt->close();
    }
}

if(isset($_POST["changeStock"])) {
    if($_POST["token"] == $token && $_SESSION["canSetStock"]) {
        $sCT = $_POST["changeType"];

        $counter = 0;

        $change_stmt = $conn->prepare("INSERT INTO Stock_Entries (StockID, ChangeType, Amount, CreatedBy) VALUES (?, ?, ?, ?)");
        $change_stmt->bind_param("iiis", $sID, $sCT, $sAmount, $userID);
        while (isset($_POST["QuantityChangeID" . $counter])) {
            $sID = $_POST["QuantityChangeID" . $counter];
            $sAmount = $_POST["QuantityChangeVal" . $counter];
            $change_stmt->execute();
            $counter += 1;
        }

        $change_stmt->close();
    }
}
$stock_stmt = $conn->prepare("SELECT si.StockID, si.StockName, CAST(si.StockPrice/100 AS DECIMAL(8, 2)), si.CurrentQuantity, si.LastModified, Modifiers.Name, Creators.Name, si.TimeCreated FROM StockInfo AS si INNER JOIN Users AS Creators ON si.UserID = Creators.UserID INNER JOIN Users AS Modifiers ON si.LastModifiedBy = Modifiers.UserID;");
$stock_stmt->bind_result($sID, $sName, $sPrice, $sQuantity, $sModDate, $sModName, $sCreatorName, $sCreatorDate);
$stock_stmt->execute();
?>

<html>
    <head>
        <title>Stock Information</title>
        <link rel="stylesheet" type="text/css" href="styles/stock.css">
        <script src="js/search.js"></script>
        <script src="js/sidebar.js"></script>
    </head>
    <body>
        <div id="sidebar" class="sidebar">
            <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
            <h1 id="welcometext"><?php echo "Welcome " .$userName ."!"; ?></h1>
            <?php
            if ($_SESSION["canAdmin"]) {
                echo "<a href='admin.php'>Admin Panel</a>";
            }
            ?>
            <a href="stock.php">Stock Information</a>
            <a href="logout.php">Log out!</a>
        </div>
        <div style="height: 20px;"></div>
        <span onclick="openNav()" id="openmenu">Menu</span>
        <div id="main">
            <?php
            if($_SESSION["canSetStock"]) {
            ?>
            <div id="addTable">
                <form action="<?php htmlentities($_SERVER["PHP_SELF"]); ?>" method="post">
                    <inpuh type='hidden' name='token' value='<?php echo $token; ?>'>
                    <table>
                        <tr>
                            <th>Stock Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                        </tr>
                        <tr>
                            <td><input type="text" name="addStockName"/></td>
                            <td><input type="number" name="addStockPrice"/></td>
                            <td><input type="number" name="addStockQuantity"/></td>
                            <td><input type="submit" name="addStock" value="Add new stock"></td>
                        </tr>
                    </table>
                </form>
            </div>
            <?php
            }
            if($_SESSION["canSeeStock"]) {
            ?>
            <form action="<?php echo htmlentities($_SERVER["PHP_SELF"]); ?>" method="post" name="QuantityForm" onsubmit="before_submit()">
                <input type='hidden' name='token' value='<?php echo $token; ?>'>
                <div id="changeOptions">
                    Action:
                    <select name="changeType">
                        <option value="1" selected>change</option>
                        <option value="2">set</option>
                    </select>
                    <input type="submit" name="changeStock" value="stock quantities">
                    <button id="resetButton" onclick="reset()">Reset</button>
                </div>
                <div id="searchtable">
                    <table>
                        <tr>
                            <th>Stock ID</th>
                            <th>Stock Name</th>
                            <th>Price</th>
                            <th>Current Quantity</th>
                            <?php
                            if ($_SESSION["canSetStock"]) {
                            ?>
                                <th>Change Quantity</th>
                            <?php
                            }
                            ?>
                            <th>Last Modified On</th>
                            <th>Last Modified By</th>
                            <th>Created By</th>
                            <th>Created On</th>
                        </tr>
                        <tr id="searchRow">
                            <td><input type="number" id="getStockID" min="1" onkeyup="showStock()"></td>
                            <td><input type="text"   id="getStockName" onkeyup="showStock()"></td>
                            <td><input type="number" id="getStockPrice" onkeyup="showStock()" step="0.01"></td>
                            <td><input type="number" id="getStockQuantity" onkeyup="showStock()"></td>
                            <?php
                            if($_SESSION["canSetStock"]) {
                            ?>
                                <td></td>
                            <?php
                            }
                            ?>
                            <td><input type="date" id="getStockModOn" onkeyup="showStock()"></td>
                            <td><input type="text"   id="getStockModBy" onkeyup="showStock()"></td>
                            <td><input type="text"   id="getStockCreateBy" onkeyup="showStock()"></td>
                            <td><input type="date" id="getStockCreateOn" onkeyup="showStock()"></td>
                        </tr>
                    </table>
                </div>
                <div id="newTable">
                    <table>
                        <?php
                        $counter = 0;
                        while ($stock_stmt->fetch()) {
                            echo "<tr>";
                            echo "<td>" .$sID ."</td>";
                            echo "<td>" .$sName ."</td>";
                            echo "<td>" .$sPrice ."</td>";
                            echo "<td>" .$sQuantity ."</td>";
                            if ($_SESSION["canSetStock"]) {
                        ?>
                            <td>
                                <input type="hidden" name="QuantityChangeID<?php echo $counter; ?>" value="<?php echo $sID; ?>">
                                <input type="number" name="QuantityChangeVal<?php echo $counter; ?>" class="QuantityChange" onchange="add('QuantityChangeVal<?php echo $counter;?>')">
                            </td>
                            <?php
                            }
                            echo "<td>" .$sModDate ."</td>";
                            echo "<td>" .$sModName ."</td>";
                            echo "<td>" .$sCreatorName ."</td>";
                            echo "<td>" .$sCreatorDate ."</td>";
                            echo "</tr>";
                            $counter += 1;
                            }
                            ?>
                    </table>
                </div>
            </form>
            <?php
            } else {
                echo "<div style='margin: 0 auto; text-align: center;'>Looks like you don't have permissions to see stock information, contact your Administrator if this is incorrect.</div>";
            }
            ?>
        </div>
    </body>
</html>
<?php $conn->close(); ?>