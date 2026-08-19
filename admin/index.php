<?php

require_once __DIR__ . '/admin_check.php';
require_once __DIR__ . '/../config/database.php';

$total_users = 0;
$total_products = 0;
$total_categories = 0;
$total_orders = 0;
$total_sales = 0;

try {

    $total_users = (int)$pdo
        ->query("SELECT COUNT(*) FROM users")
        ->fetchColumn();

    $total_products = (int)$pdo
        ->query("SELECT COUNT(*) FROM products")
        ->fetchColumn();

    $total_categories = (int)$pdo
        ->query("SELECT COUNT(*) FROM categories")
        ->fetchColumn();

    $total_orders = (int)$pdo
        ->query("SELECT COUNT(*) FROM orders")
        ->fetchColumn();

    $total_sales = (float)$pdo
        ->query("
            SELECT COALESCE(SUM(total_amount), 0)
            FROM orders
            WHERE status NOT IN ('cancelled', 'failed')
        ")
        ->fetchColumn();

} catch (PDOException $e) {

    die(
        "Dashboard error: " .
        htmlspecialchars($e->getMessage())
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

    <title>Admin Dashboard - SmartCart</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #111827;
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 245px;
            background: #111827;
            color: white;
            padding: 20px;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            overflow-y: auto;
        }

        .brand {
            font-size: 21px;
            font-weight: 800;
            margin-bottom: 30px;
        }

        .brand span {
            color: #818cf8;
        }

        .admin-profile {
            padding: 14px;
            background: rgba(255,255,255,.06);
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .admin-profile strong {
            display: block;
            font-size: 13px;
        }

        .admin-profile small {
            color: #9ca3af;
            font-size: 10px;
        }

        .nav-title {
            color: #6b7280;
            font-size: 10px;
            text-transform: uppercase;
            margin: 18px 10px 8px;
        }

        .sidebar a {
            display: block;
            padding: 11px 12px;
            color: #d1d5db;
            text-decoration: none;
            border-radius: 8px;
            font-size: 12px;
            margin-bottom: 4px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #4f46e5;
            color: white;
        }

        .logout {
            margin-top: 20px;
            background: #dc2626 !important;
            color: white !important;
        }

        .main {
            margin-left: 245px;
            width: calc(100% - 245px);
            padding: 30px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .topbar h1 {
            margin: 0;
            font-size: 27px;
        }

        .topbar p {
            margin: 5px 0 0;
            color: #6b7280;
            font-size: 12px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
        }

        .stat {
            background: white;
            border: 1px solid #e5e7eb;
            padding: 20px;
            border-radius: 13px;
            box-shadow: 0 5px 15px rgba(0,0,0,.03);
        }

        .stat-icon {
            font-size: 22px;
            margin-bottom: 10px;
        }

        .stat-label {
            color: #6b7280;
            font-size: 10px;
            text-transform: uppercase;
        }

        .stat-value {
            font-size: 24px;
            font-weight: 900;
            margin-top: 5px;
        }

        .quick {
            margin-top: 25px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .quick a {
            padding: 18px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            text-decoration: none;
            color: #111827;
            font-size: 13px;
            font-weight: 700;
        }

        .quick a:hover {
            background: #eef2ff;
            color: #4338ca;
        }

        @media (max-width: 1000px) {

            .stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .quick {
                grid-template-columns: repeat(2, 1fr);
            }

        }

        @media (max-width: 700px) {

            .sidebar {
                position: static;
                width: 100%;
            }

            .admin-wrapper {
                display: block;
            }

            .main {
                width: 100%;
                margin-left: 0;
                padding: 20px;
            }

            .stats,
            .quick {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<div class="admin-wrapper">

    <aside class="sidebar">

        <div class="brand">
            🛒 Smart<span>Cart</span>
        </div>

        <div class="admin-profile">

            <strong>
                <?= htmlspecialchars(
                    $_SESSION['user']['name']
                ) ?>
            </strong>

            <small>
                Administrator
            </small>

        </div>

        <div class="nav-title">
            Dashboard
        </div>

        <a
            href="/smartcart/admin/index.php"
            class="active"
        >
            🏠 Dashboard
        </a>

        <div class="nav-title">
            Store
        </div>

        <a href="/smartcart/admin/products.php">
            📦 Products
        </a>

        <a href="/smartcart/admin/add-product.php">
            ➕ Add Product
        </a>

        <a href="/smartcart/admin/categories.php">
            🗂 Categories
        </a>

        <a href="/smartcart/admin/orders.php">
            🛍 Orders
        </a>

        <div class="nav-title">
            Management
        </div>

        <a href="/smartcart/admin/users.php">
            👥 Users
        </a>

        <a href="/smartcart/admin/analytics.php">
            📊 Analytics
        </a>

        <a
            href="/smartcart/index.php"
        >
            🌐 View Store
        </a>

        <a
            href="/smartcart/logout.php"
            class="logout"
        >
            🚪 Logout
        </a>

    </aside>


    <main class="main">

        <div class="topbar">

            <div>

                <h1>
                    Admin Dashboard
                </h1>

                <p>
                    Manage your SmartCart store.
                </p>

            </div>

        </div>


        <div class="stats">

            <div class="stat">
                <div class="stat-icon">👥</div>
                <div class="stat-label">Users</div>
                <div class="stat-value">
                    <?= $total_users ?>
                </div>
            </div>

            <div class="stat">
                <div class="stat-icon">📦</div>
                <div class="stat-label">Products</div>
                <div class="stat-value">
                    <?= $total_products ?>
                </div>
            </div>

            <div class="stat">
                <div class="stat-icon">🗂</div>
                <div class="stat-label">Categories</div>
                <div class="stat-value">
                    <?= $total_categories ?>
                </div>
            </div>

            <div class="stat">
                <div class="stat-icon">🛍</div>
                <div class="stat-label">Orders</div>
                <div class="stat-value">
                    <?= $total_orders ?>
                </div>
            </div>

            <div class="stat">
                <div class="stat-icon">💰</div>
                <div class="stat-label">Sales</div>
                <div class="stat-value">
                    ₹<?= number_format($total_sales, 2) ?>
                </div>
            </div>

        </div>


        <div class="quick">

            <a href="/smartcart/admin/products.php">
                📦 Manage Products
            </a>

            <a href="/smartcart/admin/add-product.php">
                ➕ Add Product
            </a>

            <a href="/smartcart/admin/categories.php">
                🗂 Categories
            </a>

            <a href="/smartcart/admin/orders.php">
                🛍 Orders
            </a>

            <a href="/smartcart/admin/users.php">
                👥 Users
            </a>

            <a href="/smartcart/admin/analytics.php">
                📊 Analytics
            </a>

        </div>

    </main>

</div>

</body>
</html>