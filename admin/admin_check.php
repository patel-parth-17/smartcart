<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (
    !isset($_SESSION['user']) ||
    !is_array($_SESSION['user'])
) {

    header(
        "Location: /smartcart/login.php"
    );

    exit;
}

$role = strtolower(
    trim(
        (string)(
            $_SESSION['user']['role'] ?? ''
        )
    )
);

if ($role !== 'admin') {

    header(
        "Location: /smartcart/index.php"
    );

    exit;
}

?>