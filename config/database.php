<?php

/*
|--------------------------------------------------------------------------
| SmartCart Database Connection
|--------------------------------------------------------------------------
*/

$host = "localhost";
$dbname = "smartcart";
$username = "root";
$password = "";

try {

    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $e) {

    die(
        "<div style='
            font-family:Arial;
            padding:30px;
            margin:30px;
            background:#fee2e2;
            color:#991b1b;
            border:1px solid #fecaca;
            border-radius:12px;
        '>
            <h2>SmartCart Database Error</h2>
            <p>" .
            htmlspecialchars($e->getMessage()) .
            "</p>
        </div>"
    );
}

?>