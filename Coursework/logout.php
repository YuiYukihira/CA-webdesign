<?php
// Only attempt to log out a user if they are logged in
session_start();
if (isset($_SESSION["use"])) {
    // Remove all the varibles set in this session
    session_unset();
    // Remove this session from the list of active sessions
    session_destroy();
}
// Redirect the user to the login  page
header("location: index.php");
?>
