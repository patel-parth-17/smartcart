<?php

if (!isset($page_title)) {
    $page_title = 'SmartCart';
}

$is_admin_page =
    strpos($_SERVER['PHP_SELF'], '/admin/') !== false;

$base = $is_admin_page ? '../' : '';

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= e($page_title) ?>
    </title>

    <link
        rel="stylesheet"
        href="<?= $base ?>assets/css/style.css"
    >

</head>

<body>