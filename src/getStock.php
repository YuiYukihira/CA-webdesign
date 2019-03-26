<?php

session_start();
// only return anything if the user is logged in.
if(isset($_SESSION["use"])) {
    $userID = $_SESSION["use"];
    $userName = $_SESSION["userName"];
    $name = $_GET["name"];

    include("db_conn.php");
?>

<html>
    <body>
        <table>
            <?php
            $conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbdatabase);

            if($conn->connect_error) {
                die("Connection Failed: " .$conn->connection_error);
            }

            $get_query = "SELECT si.StockID, si.StockName,
CAST(si.StockPrice/100 AS DECIMAL(8, 2)),
si.CurrentQuantity,
si.LastModified,
Modi.Name,
Creators.Name,
si.TimeCreated
FROM StockInfo AS si
INNER JOIN Users AS Creators ON si.UserID = Creators.UserID
INNER JOIN Users AS Modi ON si.UserID = Modi.UserID
WHERE si.StockName LIKE ?";
            //dynamically create our query to include only the search terms that are set.
            $typeParams = "s";
            $params = array();
            if($name == "*") {
                $name = "%";
            } else {
                $name = "%" .$name ."%";
            }
            $params[] = &$name;

            if(isset($_GET["id"])) {
                $typeParams .= "i";
                $params[] = &$_GET["id"];
                $get_query .= " AND si.StockID = ?";
            }

            if(isset($_GET["price"])) {
                $typeParams .= "d";
                $params[] = &$_GET["price"];
                $get_query .= " AND si.stockprice = CAST(?*100 AS INT)";
            }

            if(isset($_GET["quantity"])) {
                $typeParams .= "i";
                $params[] = &$_GET["quantity"];
                $get_query .= " AND si.CurrentQuantity = ?";
            }

            if(isset($_GET["modon"])) {
                $typeParams .= "s";
                $params[] = &$_GET["modon"];
                $get_query .= " AND date_format(si.LastModified, '%Y-%m-%d') = ?";
            }

            if(isset($_GET["modby"])) {
                $typeParams .= "s";
                $mSearch = $_GET["modby"] . "%";
                $params[] = &$mSearch;
                $get_query .= " AND Modi.Name LIKE ?";
            }

            if(isset($_GET["createby"])) {
                $typeParams .= "s";
                $cSearch = $_GET["createby"] . "%";
                $params[] = &$cSearch;
                $get_query .= " AND Creators.Name LIKE ?";
            }

            if(isset($_GET["createon"])) {
                $typeParams .= "s";
                $params[] = &$_GET["createon"];
                $get_query .= " AND date_format(si.TimeCreated, '%Y-%m-%d') = ?";
            }

            $get_stmt = $conn->prepare($get_query);
            call_user_func_array(array($get_stmt, "bind_param"), array_merge(array($typeParams), $params));
            $get_stmt->bind_result($sID, $sName, $sPrice, $sQuan, $sModDate, $sModName, $sCreateName, $sCreateDate);
            $get_stmt->execute();
            $counter = 0;
            // Create a now for each of our results
            while($get_stmt->fetch()) {
                echo "<tr>";
                echo "<td>" .$sID ."</td>";
                echo "<td>" .$sName ."</td>";
                echo "<td>" .$sPrice ."</td>";
                echo "<td>" .$sQuan ."</td>";
                ?>
                <td>
                    <input type='hidden' name='QuantityChangeID<?php echo $counter; ?>' value="<?php echo $sID; ?>">
                    <input type='number' name='QuantityChangeVal<?php echo $counter; ?>'  class='QuantityChange' onchange="add('QuantityChangeVal<?php echo $counter; ?>')">
                </td>
                <?php
                echo "<td>" .$sModDate ."</td>";
                echo "<td>" .$sModName ."</td>";
                echo "<td>" .$sCreateName ."</td>";
                echo "<td>" .$sCreateDate ."</td>";
                echo "</tr>";
                $counter += 1;
            }

            $get_stmt->close();
            $conn->close();
            ?>
        </table>
    </body>
</html>
<?php
}
?>
