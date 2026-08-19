<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| Escape HTML
|--------------------------------------------------------------------------
*/

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}


/*
|--------------------------------------------------------------------------
| Currency
|--------------------------------------------------------------------------
*/

function currency($amount)
{
    return '₹' . number_format(
        (float)$amount,
        2
    );
}


/*
|--------------------------------------------------------------------------
| Current User
|--------------------------------------------------------------------------
*/

function current_user()
{
    return $_SESSION['user'] ?? null;
}


/*
|--------------------------------------------------------------------------
| Login Check
|--------------------------------------------------------------------------
*/

function is_logged_in()
{
    return isset($_SESSION['user']);
}


/*
|--------------------------------------------------------------------------
| Admin Check
|--------------------------------------------------------------------------
*/

function is_admin()
{
    return (
        isset($_SESSION['user']) &&
        (
            strtolower(
                trim(
                    $_SESSION['user']['role'] ?? ''
                )
            )
            === 'admin'
        )
    );
}


/*
|--------------------------------------------------------------------------
| Require Login
|--------------------------------------------------------------------------
*/

function require_login()
{
    if (!is_logged_in()) {

        header(
            'Location: /smartcart/login.php'
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Product Image
|--------------------------------------------------------------------------
*/

function product_image($image)
{
    $image = trim((string)$image);

    if ($image === '') {

        return
            '/smartcart/assets/images/no-image.png';
    }


    if (
        strpos($image, 'http://') === 0 ||
        strpos($image, 'https://') === 0
    ) {

        return $image;
    }


    if (
        strpos($image, '/smartcart/') === 0
    ) {

        return $image;
    }


    if (
        strpos($image, 'uploads/') === 0
    ) {

        return '/smartcart/' . $image;
    }


    return
        '/smartcart/uploads/' .
        ltrim($image, '/');
}

?>