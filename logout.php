<?php
    // logout.php
    session_start();
    session_destroy();
    header("Location: login+register/pagelogin.php");
    exit();
?>