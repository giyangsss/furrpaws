<?php
session_start();
session_destroy(); // Destroy all session data
header("Location: index.html"); // Redirect to the login page or another page after logout
exit();
?>
