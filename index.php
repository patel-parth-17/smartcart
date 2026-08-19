<?php

/* =========================================================
   SMARTCART HOME PAGE
   File:
   C:\xampp\htdocs\smartcart\index.php
   ========================================================= */

error_reporting(E_ALL);
ini_set('display_errors', '1');


/* =========================================================
   SESSION
   ========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   DATABASE
   ========================================================= */

require_once __DIR__ . '/config/database.php';


/* =========================================================
   CHECK DATABASE
   ========================================================= */

if (!isset($pdo) || !($pdo instanceof PDO)) {

    die(
        '<div style="font-family:Arial;padding:30px;margin:30px;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;border-radius:12px;">
            <h2>SmartCart Database Error</h2>
            <p>$pdo was not created.</p>
            <p>Please check:</p>
            <ul>
                <li>C:\xampp\htdocs\smartcart\config\database.php</li>
                <li>MySQL is running in XAMPP</li>
                <li>Database name is correct</li>
            </ul>
        </div>'
    );
}


/* =========================================================
   USER
   ========================================================= */

$is_logged_in = isset($_SESSION['user']);

$user_name = '';

$user_role = 'user';

if ($is_logged_in) {

    $user_name =
        $_SESSION['user']['name'] ?? '';

    $user_role =
        strtolower(
            trim(
                (string)(
                    $_SESSION['user']['role']
                    ?? 'user'
                )
            )
        );
}


/* =========================================================
   PRODUCTS
   ========================================================= */

$products = [];

try {

    $stmt = $pdo->query("
        SELECT
            p.id,
            p.name,
            p.description,
            p.price,
            p.stock,
            p.image,
            p.brand,
            p.rating,
            p.created_at,
            c.name AS category_name

        FROM products p

        LEFT JOIN categories c
            ON p.category_id = c.id

        ORDER BY p.created_at DESC

        LIMIT 8
    ");

    $products =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $products = [];

}


/* =========================================================
   CATEGORIES
   ========================================================= */

$categories = [];

try {

    $stmt = $pdo->query("
        SELECT
            id,
            name,
            description

        FROM categories

        ORDER BY name ASC

        LIMIT 8
    ");

    $categories =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $categories = [];

}


/* =========================================================
   IMAGE FUNCTION
   ========================================================= */

function smartcart_home_image($image)
{
    $image =
        trim(
            (string)$image
        );


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
        strpos($image, '/') === 0
    ) {

        return $image;
    }


    if (
        strpos($image, 'uploads/') === 0
    ) {

        return
            '/smartcart/' . $image;
    }


    return
        '/smartcart/uploads/' .
        ltrim(
            $image,
            '/'
        );
}

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
        SmartCart - Online Shopping
    </title>


    <!-- =====================================================
         MAIN CSS
         ===================================================== -->

    <link
        rel="stylesheet"
        href="/smartcart/assets/css/style.css"
    >


    <!-- =====================================================
         HOMEPAGE EXTRA CSS
         ===================================================== -->

    <style>

        /* =====================================================
           RESET
           ===================================================== */

        * {
            box-sizing: border-box;
        }


        html {
            scroll-behavior: smooth;
        }


        body {
            margin: 0;

            background: #f5f7fb;

            color: #111827;

            font-family:
                Arial,
                Helvetica,
                sans-serif;
        }


        a {
            text-decoration: none;
        }


        button,
        input,
        textarea {
            font-family: inherit;
        }


        /* =====================================================
           CONTAINER
           ===================================================== */

        .container {
            width: 92%;

            max-width: 1200px;

            margin: 0 auto;
        }


        /* =====================================================
           NAVBAR
           ===================================================== */

        .smart-navbar {
            width: 100%;

            background: white;

            border-bottom:
                1px solid #e5e7eb;

            box-shadow:
                0 3px 15px
                rgba(0,0,0,.05);

            position: sticky;

            top: 0;

            z-index: 9999;
        }


        .smart-nav-inner {
            width: 92%;

            max-width: 1200px;

            min-height: 70px;

            margin: auto;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;
        }


        .smart-logo {
            display: flex;

            align-items: center;

            gap: 9px;

            color: #111827;

            font-size: 22px;

            font-weight: 900;
        }


        .smart-logo-icon {
            width: 41px;

            height: 41px;

            display: flex;

            align-items: center;

            justify-content: center;

            background:
                linear-gradient(
                    135deg,
                    #4f46e5,
                    #7c3aed
                );

            border-radius: 11px;

            color: white;

            font-size: 20px;
        }


        .smart-logo-text span {
            color: #4f46e5;
        }


        .smart-nav-links {
            display: flex;

            align-items: center;

            gap: 4px;
        }


        .smart-nav-links a {
            color: #4b5563;

            padding:
                9px 11px;

            border-radius: 8px;

            font-size: 12px;

            font-weight: 600;

            transition: .2s;
        }


        .smart-nav-links a:hover {
            background: #eef2ff;

            color: #4f46e5;
        }


        .smart-nav-user {
            display: flex;

            align-items: center;

            gap: 5px;
        }


        .smart-nav-user a {
            color: #4b5563;

            padding:
                8px 11px;

            border-radius: 8px;

            font-size: 12px;

            font-weight: 600;
        }


        .smart-nav-user a:hover {
            background: #eef2ff;

            color: #4f46e5;
        }


        .smart-welcome {
            color: #4f46e5;

            font-size: 11px;
        }


        .smart-admin-link {
            background: #4f46e5;

            color: white !important;
        }


        .smart-admin-link:hover {
            background: #4338ca !important;

            color: white !important;
        }


        .smart-logout {
            color: #dc2626 !important;
        }


        /* =====================================================
           HERO
           ===================================================== */

        .home-hero {
            padding:
                35px 0 20px;

            background:
                radial-gradient(
                    circle at 90% 10%,
                    rgba(99,102,241,.14),
                    transparent 28%
                ),
                #f5f7fb;
        }


        .hero-content {
            min-height: 450px;

            display: grid;

            grid-template-columns:
                1.25fr .75fr;

            align-items: center;

            gap: 50px;

            padding: 55px;

            position: relative;

            overflow: hidden;

            border-radius: 28px;

            background:
                linear-gradient(
                    135deg,
                    #111827,
                    #312e81,
                    #4f46e5
                );

            box-shadow:
                0 25px 60px
                rgba(31,41,55,.20);
        }


        .hero-content::before {
            content: "";

            position: absolute;

            width: 340px;

            height: 340px;

            right: -110px;

            top: -160px;

            border-radius: 50%;

            background:
                rgba(255,255,255,.07);
        }


        .hero-content::after {
            content: "";

            position: absolute;

            width: 180px;

            height: 180px;

            left: 50%;

            bottom: -110px;

            border-radius: 50%;

            background:
                rgba(255,255,255,.04);
        }


        .hero-text {
            position: relative;

            z-index: 4;
        }


        .hero-label {
            display: inline-block;

            padding:
                8px 14px;

            margin-bottom: 18px;

            border-radius: 50px;

            background:
                rgba(255,255,255,.12);

            border:
                1px solid
                rgba(255,255,255,.20);

            color: #ddd6fe;

            font-size: 12px;

            font-weight: 700;
        }


        .hero-text h1 {
            margin: 0 0 18px;

            color: white;

            font-size:
                clamp(
                    42px,
                    5vw,
                    64px
                );

            line-height: 1.05;

            letter-spacing: -2px;

            font-weight: 900;
        }


        .hero-text h1 span {
            color: #c4b5fd;
        }


        .hero-text p {
            max-width: 570px;

            margin:
                0 0 28px;

            color: #dbeafe;

            font-size: 15px;

            line-height: 1.8;
        }


        /* =====================================================
           HERO BUTTONS
           ===================================================== */

        .hero-buttons {
            display: flex;

            gap: 12px;

            flex-wrap: wrap;
        }


        .home-btn {
            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-height: 44px;

            padding:
                0 20px;

            border-radius: 10px;

            font-size: 12px;

            font-weight: 700;

            cursor: pointer;

            transition: .2s;

            border: none;
        }


        .home-btn-primary {
            background: white;

            color: #4338ca;
        }


        .home-btn-primary:hover {
            background: #eef2ff;

            transform:
                translateY(-2px);
        }


        .home-btn-outline {
            background:
                rgba(255,255,255,.10);

            border:
                1px solid
                rgba(255,255,255,.25);

            color: white;
        }


        .home-btn-outline:hover {
            background:
                rgba(255,255,255,.17);

            transform:
                translateY(-2px);
        }


        /* =====================================================
           AI BOX
           ===================================================== */

        .hero-box {
            width: 100%;

            max-width: 340px;

            margin: 0 auto;

            padding: 30px;

            position: relative;

            z-index: 5;

            border-radius: 20px;

            background:
                rgba(255,255,255,.10);

            border:
                1px solid
                rgba(255,255,255,.20);

            backdrop-filter: blur(12px);

            text-align: center;

            color: white;

            box-shadow:
                0 20px 50px
                rgba(0,0,0,.18);
        }


        .hero-box-icon {
            width: 70px;

            height: 70px;

            margin:
                0 auto 16px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 20px;

            background:
                rgba(255,255,255,.14);

            font-size: 31px;
        }


        .hero-box h3 {
            margin:
                0 0 8px;

            color: white;

            font-size: 21px;
        }


        .hero-box p {
            margin:
                0 0 20px;

            color: #e0e7ff;

            font-size: 12px;

            line-height: 1.7;
        }


        .hero-ai-button {
            width: 100%;

            height: 42px;

            border: none;

            border-radius: 9px;

            background: white;

            color: #4338ca;

            font-size: 12px;

            font-weight: 700;

            cursor: pointer;

            transition: .2s;
        }


        .hero-ai-button:hover {
            background: #eef2ff;

            transform:
                translateY(-2px);
        }


        /* =====================================================
           FEATURES
           ===================================================== */

        .features-section {
            padding:
                20px 0 10px;
        }


        .feature-grid {
            display: grid;

            grid-template-columns:
                repeat(4,1fr);

            gap: 15px;
        }


        .feature-card {
            padding: 20px;

            background: white;

            border:
                1px solid #e5e7eb;

            border-radius: 14px;

            box-shadow:
                0 5px 18px
                rgba(0,0,0,.035);

            transition: .2s;
        }


        .feature-card:hover {
            transform:
                translateY(-4px);

            box-shadow:
                0 14px 30px
                rgba(79,70,229,.09);
        }


        .feature-icon {
            width: 45px;

            height: 45px;

            display: flex;

            align-items: center;

            justify-content: center;

            margin-bottom: 12px;

            border-radius: 12px;

            background: #eef2ff;

            font-size: 20px;
        }


        .feature-card h3 {
            margin:
                0 0 6px;

            font-size: 14px;

            color: #111827;
        }


        .feature-card p {
            margin: 0;

            color: #6b7280;

            font-size: 11px;

            line-height: 1.6;
        }


        /* =====================================================
           HOME SECTION
           ===================================================== */

        .home-section {
            padding:
                45px 0 0;
        }


        .products-section {
            padding-bottom: 65px;
        }


        .section-title {
            display: flex;

            align-items: flex-end;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 20px;
        }


        .section-title h2 {
            margin: 0;

            color: #111827;

            font-size: 27px;

            font-weight: 800;
        }


        .section-title p {
            margin:
                6px 0 0;

            color: #6b7280;

            font-size: 12px;
        }


        .view-all {
            color: #4f46e5;

            font-size: 12px;

            font-weight: 700;

            white-space: nowrap;
        }


        .view-all:hover {
            color: #3730a3;
        }


        /* =====================================================
           CATEGORIES
           ===================================================== */

        .category-grid {
            display: grid;

            grid-template-columns:
                repeat(4,1fr);

            gap: 16px;
        }


        .category-card {
            min-height: 145px;

            padding: 20px;

            display: flex;

            flex-direction: column;

            justify-content: flex-end;

            position: relative;

            overflow: hidden;

            border-radius: 15px;

            border:
                1px solid #e5e7eb;

            background:
                linear-gradient(
                    135deg,
                    #ffffff,
                    #eef2ff
                );

            transition: .2s;
        }


        .category-card:hover {
            transform:
                translateY(-4px);

            border-color:
                #c7d2fe;

            box-shadow:
                0 15px 30px
                rgba(79,70,229,.10);
        }


        .category-icon {
            position: absolute;

            right: 15px;

            top: 8px;

            font-size: 55px;

            opacity: .12;

            transition: .2s;
        }


        .category-card:hover
        .category-icon {
            opacity: .20;

            transform:
                scale(1.05)
                rotate(4deg);
        }


        .category-card h3 {
            margin: 0;

            color: #111827;

            font-size: 16px;
        }


        .category-card p {
            margin:
                5px 0 0;

            color: #6b7280;

            font-size: 10px;

            line-height: 1.5;
        }


        /* =====================================================
           PRODUCT GRID
           ===================================================== */

        .product-grid {
            display: grid;

            grid-template-columns:
                repeat(4,minmax(0,1fr));

            gap: 18px;
        }


        .product-card {
            overflow: hidden;

            background: white;

            border:
                1px solid #e5e7eb;

            border-radius: 15px;

            box-shadow:
                0 5px 18px
                rgba(15,23,42,.04);

            transition: .2s;
        }


        .product-card:hover {
            transform:
                translateY(-5px);

            box-shadow:
                0 18px 35px
                rgba(15,23,42,.10);
        }


        .product-image {
            width: 100%;

            height: 220px;

            overflow: hidden;

            background:
                linear-gradient(
                    135deg,
                    #f8fafc,
                    #eef2ff
                );
        }


        .product-image img {
            width: 100%;

            height: 100%;

            display: block;

            object-fit: contain;

            padding: 12px;

            transition: .3s;
        }


        .product-card:hover
        .product-image img {
            transform:
                scale(1.06);
        }


        .product-card-body {
            padding: 16px;
        }


        .product-brand {
            display: block;

            margin-bottom: 5px;

            color: #6366f1;

            font-size: 10px;

            font-weight: 700;

            text-transform: uppercase;
        }


        .product-card-body h3 {
            min-height: 42px;

            margin:
                0 0 7px;

            color: #111827;

            font-size: 15px;

            line-height: 1.4;
        }


        .product-rating {
            margin-bottom: 7px;

            color: #f59e0b;

            font-size: 11px;
        }


        .product-price {
            margin-bottom: 6px;

            color: #111827;

            font-size: 20px;

            font-weight: 900;
        }


        .stock-available {
            display: block;

            margin-bottom: 12px;

            color: #15803d;

            font-size: 10px;

            font-weight: 700;
        }


        .stock-out {
            display: block;

            margin-bottom: 12px;

            color: #b91c1c;

            font-size: 10px;

            font-weight: 700;
        }


        .product-button {
            width: 100%;

            height: 40px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 8px;

            background: #4f46e5;

            color: white;

            font-size: 11px;

            font-weight: 700;
        }


        .product-button:hover {
            background: #4338ca;
        }


        /* =====================================================
           EMPTY STATE
           ===================================================== */

        .empty-state {
            padding:
                55px 25px;

            background: white;

            border:
                1px dashed #d1d5db;

            border-radius: 15px;

            text-align: center;
        }


        .empty-state h3 {
            margin: 0 0 6px;

            color: #111827;
        }


        .empty-state p {
            margin: 0;

            color: #6b7280;

            font-size: 12px;
        }


        /* =====================================================
           AI FLOATING BUTTON
           ===================================================== */

        .ai-floating-button {
            position: fixed;

            right: 25px;

            bottom: 25px;

            z-index: 9998;

            min-height: 48px;

            padding:
                0 17px;

            display: flex;

            align-items: center;

            gap: 8px;

            border: none;

            border-radius: 50px;

            background:
                linear-gradient(
                    135deg,
                    #4f46e5,
                    #7c3aed
                );

            color: white;

            font-size: 12px;

            font-weight: 700;

            cursor: pointer;

            box-shadow:
                0 10px 30px
                rgba(79,70,229,.35);

            transition: .2s;
        }


        .ai-floating-button:hover {
            transform:
                translateY(-4px);

            box-shadow:
                0 15px 35px
                rgba(79,70,229,.45);
        }


        /* =====================================================
           AI MODAL
           ===================================================== */

        .ai-modal {
            display: none;

            position: fixed;

            inset: 0;

            z-index: 10000;
        }


        .ai-modal.active {
            display: flex;

            align-items: center;

            justify-content: center;
        }


        .ai-modal-overlay {
            position: absolute;

            inset: 0;

            background:
                rgba(15,23,42,.65);

            backdrop-filter:
                blur(5px);
        }


        .ai-modal-box {
            width: 95%;

            max-width: 520px;

            max-height: 90vh;

            overflow: hidden;

            position: relative;

            z-index: 2;

            background: white;

            border-radius: 18px;

            box-shadow:
                0 30px 80px
                rgba(0,0,0,.25);
        }


        .ai-modal-header {
            min-height: 70px;

            padding:
                14px 18px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            background:
                linear-gradient(
                    135deg,
                    #4f46e5,
                    #7c3aed
                );

            color: white;
        }


        .ai-title {
            display: flex;

            align-items: center;

            gap: 10px;
        }


        .ai-big-icon {
            width: 42px;

            height: 42px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 12px;

            background:
                rgba(255,255,255,.15);

            font-size: 21px;
        }


        .ai-title h2 {
            margin: 0;

            color: white;

            font-size: 17px;
        }


        .ai-title p {
            margin:
                2px 0 0;

            color: #e0e7ff;

            font-size: 10px;
        }


        .ai-close {
            width: 35px;

            height: 35px;

            border: none;

            border-radius: 50%;

            background:
                rgba(255,255,255,.14);

            color: white;

            font-size: 22px;

            cursor: pointer;
        }


        .ai-close:hover {
            background:
                rgba(255,255,255,.25);
        }


        .ai-modal-messages {
            height: 390px;

            overflow-y: auto;

            padding: 18px;

            background: #f8fafc;
        }


        .ai-message {
            margin-bottom: 15px;

            display: flex;

            gap: 9px;
        }


        .ai-avatar {
            width: 33px;

            height: 33px;

            flex-shrink: 0;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 10px;

            background: #eef2ff;

            font-size: 16px;
        }


        .ai-bubble {
            max-width: 85%;

            padding:
                12px 14px;

            background: white;

            border:
                1px solid #e5e7eb;

            border-radius: 12px;

            color: #374151;

            font-size: 11px;

            line-height: 1.6;
        }


        .ai-bubble strong {
            color: #111827;
        }


        .ai-input-area {
            display: flex;

            gap: 8px;

            padding: 12px;

            background: white;

            border-top:
                1px solid #e5e7eb;
        }


        .ai-input-area input {
            flex: 1;

            height: 40px;

            padding:
                0 12px;

            border:
                1px solid #d1d5db;

            border-radius: 8px;

            outline: none;

            font-size: 11px;
        }


        .ai-input-area input:focus {
            border-color: #6366f1;
        }


        .ai-send {
            width: 75px;

            height: 40px;

            border: none;

            border-radius: 8px;

            background: #4f46e5;

            color: white;

            font-size: 11px;

            font-weight: 700;

            cursor: pointer;
        }


        .ai-send:hover {
            background: #4338ca;
        }


        /* =====================================================
           FOOTER
           ===================================================== */

        .smart-footer {
            margin-top: 40px;

            padding:
                35px 0;

            background: #111827;

            color: #9ca3af;
        }


        .smart-footer-inner {
            width: 92%;

            max-width: 1200px;

            margin: auto;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;
        }


        .smart-footer strong {
            color: white;

            font-size: 17px;
        }


        .smart-footer p {
            margin:
                5px 0 0;

            font-size: 11px;
        }


        /* =====================================================
           RESPONSIVE
           ===================================================== */

        @media (max-width: 1000px) {

            .hero-content {
                grid-template-columns: 1fr;
            }


            .hero-box {
                max-width: 400px;
            }


            .feature-grid {
                grid-template-columns:
                    repeat(2,1fr);
            }


            .category-grid,
            .product-grid {
                grid-template-columns:
                    repeat(3,1fr);
            }

        }


        @media (max-width: 750px) {

            .smart-nav-links {
                display: none;
            }


            .smart-nav-inner {
                min-height: 62px;
            }


            .hero-content {
                width: 94%;

                margin: auto;

                padding:
                    40px 25px;

                border-radius: 21px;
            }


            .hero-text h1 {
                font-size: 42px;
            }


            .feature-grid {
                width: 94%;

                margin: auto;

                grid-template-columns: 1fr;
            }


            .home-section .container {
                width: 94%;
            }


            .category-grid,
            .product-grid {
                grid-template-columns:
                    repeat(2,1fr);
            }

        }


        @media (max-width: 500px) {

            .smart-nav-user .smart-welcome {
                display: none;
            }


            .hero-text h1 {
                font-size: 35px;

                letter-spacing: -1px;
            }


            .hero-buttons {
                flex-direction: column;

                align-items: stretch;
            }


            .home-btn {
                width: 100%;
            }


            .category-grid,
            .product-grid {
                grid-template-columns: 1fr;
            }


            .product-image {
                height: 250px;
            }


            .ai-floating-button {
                width: 50px;

                height: 50px;

                padding: 0;

                justify-content: center;
            }


            .ai-floating-button span {
                display: none;
            }


            .smart-footer-inner {
                align-items: flex-start;

                flex-direction: column;
            }

        }

    </style>

</head>


<body>


<!-- =========================================================
     NAVBAR
     ========================================================= -->

<nav class="smart-navbar">

    <div class="smart-nav-inner">


        <!-- LOGO -->

        <a
            href="/smartcart/index.php"
            class="smart-logo"
        >

            <span class="smart-logo-icon">
                🛒
            </span>

            <span class="smart-logo-text">
                Smart<span>Cart</span>
            </span>

        </a>


        <!-- LINKS -->

        <div class="smart-nav-links">

            <a
                href="/smartcart/index.php"
            >
                Home
            </a>

            <a
                href="/smartcart/products.php"
            >
                Products
            </a>


            <?php if ($is_logged_in): ?>

                <a
                    href="/smartcart/cart.php"
                >
                    🛒 Cart
                </a>

                <a
                    href="/smartcart/wishlist.php"
                >
                    ♡ Wishlist
                </a>

                <a
                    href="/smartcart/orders.php"
                >
                    📦 Orders
                </a>


                <?php if (
                    $user_role === 'admin'
                ): ?>

                    <a
                        href="/smartcart/admin/index.php"
                        class="smart-admin-link"
                    >
                        Admin
                    </a>

                <?php endif; ?>

            <?php endif; ?>


        </div>


        <!-- USER -->

        <div class="smart-nav-user">


            <?php if ($is_logged_in): ?>


                <span class="smart-welcome">

                    Hi,
                    <?= htmlspecialchars(
                        $user_name
                    ) ?>

                </span>


                <a
                    href="/smartcart/logout.php"
                    class="smart-logout"
                >
                    Logout
                </a>


            <?php else: ?>


                <a
                    href="/smartcart/login.php"
                >
                    Login
                </a>


                <a
                    href="/smartcart/register.php"
                    class="smart-admin-link"
                >
                    Register
                </a>


            <?php endif; ?>


        </div>


    </div>

</nav>


<!-- =========================================================
     HERO
     ========================================================= -->

<section class="home-hero">

    <div class="container">


        <div class="hero-content">


            <!-- LEFT SIDE -->

            <div class="hero-text">


                <span class="hero-label">

                    🛍️ Welcome to SmartCart

                </span>


                <h1>

                    Smart Shopping.

                    <br>

                    <span>
                        Better Choices.
                    </span>

                </h1>


                <p>

                    Discover quality products,
                    great prices, and smart
                    recommendations all in one place.

                </p>


                <div class="hero-buttons">


                    <a
                        href="/smartcart/products.php"
                        class="home-btn home-btn-primary"
                    >
                        🛍️ Shop Now
                    </a>


                    <button
                        type="button"
                        class="home-btn home-btn-outline"
                        onclick="openAI()"
                    >
                        🤖 Ask SmartCart AI
                    </button>


                </div>


            </div>


            <!-- RIGHT AI BOX -->

            <div class="hero-box">


                <div class="hero-box-icon">
                    🤖
                </div>


                <h3>
                    SmartCart AI
                </h3>


                <p>

                    Not sure what to buy?

                    Ask our AI assistant
                    to find products
                    for you.

                </p>


                <button
                    type="button"
                    class="hero-ai-button"
                    onclick="openAI()"
                >
                    Start AI Assistant
                </button>


            </div>


        </div>


    </div>

</section>


<!-- =========================================================
     FEATURES
     ========================================================= -->

<section class="features-section">

    <div class="container">

        <div class="feature-grid">


            <div class="feature-card">

                <div class="feature-icon">
                    🛒
                </div>

                <h3>
                    Easy Shopping
                </h3>

                <p>
                    Add products to your cart
                    and checkout easily.
                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">
                    🚚
                </div>

                <h3>
                    Fast Delivery
                </h3>

                <p>
                    Get your products
                    delivered to your address.
                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">
                    🔒
                </div>

                <h3>
                    Secure
                </h3>

                <p>
                    Your account and order
                    information are protected.
                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">
                    🤖
                </div>

                <h3>
                    AI Recommendations
                </h3>

                <p>
                    Find products quickly
                    using SmartCart AI.
                </p>

            </div>


        </div>

    </div>

</section>


<!-- =========================================================
     CATEGORIES
     ========================================================= -->

<?php if (
    count($categories) > 0
): ?>

<section class="home-section">

    <div class="container">


        <div class="section-title">

            <div>

                <h2>
                    Shop by Category
                </h2>

                <p>
                    Explore our product categories.
                </p>

            </div>


            <a
                href="/smartcart/products.php"
                class="view-all"
            >
                View All →
            </a>


        </div>


        <div class="category-grid">


            <?php foreach (
                $categories as $category
            ): ?>


                <a
                    href="/smartcart/products.php?category=<?= (int)$category['id'] ?>"
                    class="category-card"
                >


                    <div class="category-icon">
                        📦
                    </div>


                    <h3>

                        <?= htmlspecialchars(
                            $category['name']
                        ) ?>

                    </h3>


                    <?php if (
                        !empty(
                            $category['description']
                        )
                    ): ?>

                        <p>

                            <?= htmlspecialchars(
                                $category['description']
                            ) ?>

                        </p>

                    <?php endif; ?>


                </a>


            <?php endforeach; ?>


        </div>


    </div>

</section>

<?php endif; ?>


<!-- =========================================================
     LATEST PRODUCTS
     ========================================================= -->

<section class="home-section products-section">

    <div class="container">


        <div class="section-title">


            <div>

                <h2>
                    Latest Products
                </h2>

                <p>
                    Browse our newest products.
                </p>

            </div>


            <a
                href="/smartcart/products.php"
                class="view-all"
            >
                View All →
            </a>


        </div>


        <?php if (
            count($products) > 0
        ): ?>


            <div class="product-grid">


                <?php foreach (
                    $products as $product
                ): ?>


                    <div class="product-card">


                        <!-- IMAGE -->

                        <div class="product-image">

                            <img
                                src="<?= htmlspecialchars(
                                    smartcart_home_image(
                                        $product['image']
                                    )
                                ) ?>"
                                alt="<?= htmlspecialchars(
                                    $product['name']
                                ) ?>"
                                loading="lazy"
                                onerror="
                                    this.onerror=null;
                                    this.src='/smartcart/assets/images/no-image.png';
                                "
                            >

                        </div>


                        <!-- BODY -->

                        <div
                            class="product-card-body"
                        >


                            <?php if (
                                !empty(
                                    $product['brand']
                                )
                            ): ?>

                                <span
                                    class="product-brand"
                                >

                                    <?= htmlspecialchars(
                                        $product['brand']
                                    ) ?>

                                </span>

                            <?php endif; ?>


                            <h3>

                                <?= htmlspecialchars(
                                    $product['name']
                                ) ?>

                            </h3>


                            <div
                                class="product-rating"
                            >

                                ⭐

                                <?= number_format(
                                    (float)(
                                        $product['rating']
                                        ?? 0
                                    ),
                                    1
                                ) ?>

                                / 5

                            </div>


                            <div
                                class="product-price"
                            >

                                ₹<?= number_format(
                                    (float)$product['price'],
                                    2
                                ) ?>

                            </div>


                            <?php if (
                                (int)$product['stock'] > 0
                            ): ?>

                                <span
                                    class="stock-available"
                                >
                                    ✓ In Stock
                                </span>

                            <?php else: ?>

                                <span
                                    class="stock-out"
                                >
                                    Out of Stock
                                </span>

                            <?php endif; ?>


                            <a
                                href="/smartcart/product.php?id=<?= (int)$product['id'] ?>"
                                class="product-button"
                            >
                                View Product
                            </a>


                        </div>


                    </div>


                <?php endforeach; ?>


            </div>


        <?php else: ?>


            <div class="empty-state">

                <h3>
                    No products available
                </h3>

                <p>
                    Products will appear here when
                    they are added to SmartCart.
                </p>

            </div>


        <?php endif; ?>


    </div>

</section>


<!-- =========================================================
     AI FLOATING BUTTON
     ========================================================= -->

<button
    type="button"
    class="ai-floating-button"
    onclick="openAI()"
    title="Open SmartCart AI"
>

    🤖

    <span>
        AI Assistant
    </span>

</button>


<!-- =========================================================
     AI MODAL
     ========================================================= -->

<div
    id="aiModal"
    class="ai-modal"
>


    <!-- OVERLAY -->

    <div
        class="ai-modal-overlay"
        onclick="closeAI()"
    ></div>


    <!-- MODAL -->

    <div class="ai-modal-box">


        <!-- HEADER -->

        <div class="ai-modal-header">


            <div class="ai-title">


                <div class="ai-big-icon">
                    🤖
                </div>


                <div>

                    <h2>
                        SmartCart AI
                    </h2>

                    <p>
                        Your personal shopping assistant
                    </p>

                </div>


            </div>


            <button
                type="button"
                class="ai-close"
                onclick="closeAI()"
            >
                ×
            </button>


        </div>


        <!-- MESSAGES -->

        <div
            id="aiMessages"
            class="ai-modal-messages"
        >


            <div class="ai-message">


                <div class="ai-avatar">
                    🤖
                </div>


                <div class="ai-bubble">

                    <strong>
                        SmartCart AI
                    </strong>


                    <p>

                        Hello
                        <?= $is_logged_in
                            ? htmlspecialchars(
                                $user_name
                            )
                            : 'there' ?>! 👋

                    </p>


                    <p>

                        Tell me what you're
                        looking for and I'll
                        help you find products
                        from SmartCart.

                    </p>


                    <p>

                        Example:
                        "I need a laptop"

                    </p>


                </div>


            </div>


        </div>


        <!-- INPUT -->

        <div class="ai-input-area">


            <input
                type="text"
                id="aiQuestion"
                placeholder="Ask about products..."
                onkeydown="
                    if(event.key === 'Enter'){
                        askSmartCartAI();
                    }
                "
            >


            <button
                type="button"
                class="ai-send"
                onclick="askSmartCartAI()"
            >
                Send
            </button>


        </div>


    </div>

</div>


<!-- =========================================================
     FOOTER
     ========================================================= -->

<footer class="smart-footer">

    <div class="smart-footer-inner">


        <div>

            <strong>
                🛒 SmartCart
            </strong>

            <p>
                Smart shopping made simple.
            </p>

        </div>


        <div>

            <p>
                © <?= date('Y') ?>
                SmartCart
            </p>

        </div>


    </div>

</footer>


<!-- =========================================================
     AI JAVASCRIPT
     ========================================================= -->

<script>


/* =========================================================
   OPEN AI
   ========================================================= */

function openAI()
{
    const modal =
        document.getElementById(
            'aiModal'
        );


    if (!modal) {
        return;
    }


    modal.classList.add(
        'active'
    );


    const input =
        document.getElementById(
            'aiQuestion'
        );


    if (input) {

        setTimeout(
            function() {

                input.focus();

            },
            200
        );
    }
}


/* =========================================================
   CLOSE AI
   ========================================================= */

function closeAI()
{
    const modal =
        document.getElementById(
            'aiModal'
        );


    if (modal) {

        modal.classList.remove(
            'active'
        );
    }
}


/* =========================================================
   ASK AI
   ========================================================= */

async function askSmartCartAI()
{

    const input =
        document.getElementById(
            'aiQuestion'
        );


    const messages =
        document.getElementById(
            'aiMessages'
        );


    if (
        !input ||
        !messages
    ) {

        return;
    }


    const question =
        input.value.trim();


    if (
        question === ''
    ) {

        return;
    }


    /* USER MESSAGE */

    const userMessage =
        document.createElement(
            'div'
        );


    userMessage.className =
        'ai-message';


    userMessage.innerHTML =

        '<div class="ai-avatar">👤</div>' +

        '<div class="ai-bubble">' +

            '<strong>You</strong>' +

            '<p>' +
                escapeAIText(question) +
            '</p>' +

        '</div>';


    messages.appendChild(
        userMessage
    );


    input.value = '';


    messages.scrollTop =
        messages.scrollHeight;


    /* LOADING */

    const loading =
        document.createElement(
            'div'
        );


    loading.className =
        'ai-message';


    loading.id =
        'aiLoading';


    loading.innerHTML =

        '<div class="ai-avatar">🤖</div>' +

        '<div class="ai-bubble">' +

            '<strong>SmartCart AI</strong>' +

            '<p>Thinking... ⏳</p>' +

        '</div>';


    messages.appendChild(
        loading
    );


    messages.scrollTop =
        messages.scrollHeight;


    try {


        const formData =
            new FormData();


        formData.append(
            'question',
            question
        );


        const response =
            await fetch(
                '/smartcart/api/gemini.php',
                {
                    method: 'POST',
                    body: formData
                }
            );


        const data =
            await response.json();


        const oldLoading =
            document.getElementById(
                'aiLoading'
            );


        if (oldLoading) {

            oldLoading.remove();

        }


        let responseText =
            'Sorry, I could not find an answer.';


        if (
            data &&
            data.response
        ) {

            responseText =
                data.response;

        } else if (
            data &&
            data.message
        ) {

            responseText =
                data.message;

        }


        /* AI MESSAGE */

        const aiMessage =
            document.createElement(
                'div'
            );


        aiMessage.className =
            'ai-message';


        aiMessage.innerHTML =

            '<div class="ai-avatar">🤖</div>' +

            '<div class="ai-bubble">' +

                '<strong>SmartCart AI</strong>' +

                '<p>' +
                    escapeAIText(
                        responseText
                    ) +
                '</p>' +

            '</div>';


        messages.appendChild(
            aiMessage
        );


        /* PRODUCT RESULTS */

        if (
            data &&
            Array.isArray(
                data.products
            ) &&
            data.products.length > 0
        ) {


            const productBox =
                document.createElement(
                    'div'
                );


            productBox.className =
                'ai-message';


            let html =

                '<div class="ai-avatar">🛍️</div>' +

                '<div class="ai-bubble">' +

                '<strong>Recommended Products</strong>';


            data.products.forEach(
                function(product)
                {

                    html +=

                        '<p>' +

                        '<a href="/smartcart/product.php?id=' +
                        Number(product.id) +
                        '" style="color:#4f46e5;font-weight:700;">' +

                        escapeAIText(
                            product.name
                            || 'Product'
                        ) +

                        '</a>' +

                        '<br>₹' +

                        Number(
                            product.price || 0
                        ).toFixed(2) +

                        '</p>';

                }
            );


            html +=
                '</div>';


            productBox.innerHTML =
                html;


            messages.appendChild(
                productBox
            );
        }


    } catch (error) {


        const oldLoading =
            document.getElementById(
                'aiLoading'
            );


        if (oldLoading) {

            oldLoading.remove();

        }


        const errorMessage =
            document.createElement(
                'div'
            );


        errorMessage.className =
            'ai-message';


        errorMessage.innerHTML =

            '<div class="ai-avatar">🤖</div>' +

            '<div class="ai-bubble">' +

                '<strong>SmartCart AI</strong>' +

                '<p>' +

                'Unable to connect to the AI service right now.' +

                '</p>' +

            '</div>';


        messages.appendChild(
            errorMessage
        );

    }


    messages.scrollTop =
        messages.scrollHeight;
}


/* =========================================================
   ESCAPE TEXT
   ========================================================= */

function escapeAIText(text)
{

    const div =
        document.createElement(
            'div'
        );


    div.textContent =
        String(text);


    return div.innerHTML;
}


/* =========================================================
   ESC KEY
   ========================================================= */

document.addEventListener(
    'keydown',
    function(event)
    {

        if (
            event.key === 'Escape'
        ) {

            closeAI();

        }

    }
);


</script>


</body>

</html>