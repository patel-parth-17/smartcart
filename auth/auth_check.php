<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {

    header(
        'Location: /smartcart/login.php'
    );

    exit;
}

?>