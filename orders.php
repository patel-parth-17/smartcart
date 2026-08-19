<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';


/* =========================================================
   LOGIN
   ========================================================= */

if (!isset($_SESSION['user'])) {

    header(
        'Location: /smartcart/login.php'
    );

    exit;
}


$user_id =
    (int)$_SESSION['user']['id'];


/* =========================================================
   ORDERS
   ========================================================= */

$orders = [];

$error = '';

try {

    $stmt =
        $pdo->prepare("
            SELECT

                o.id,
                o.total_amount,
                o.shipping_address,
                o.payment_method,
                o.status,
                o.created_at

            FROM orders o

            WHERE o.user_id = ?

            ORDER BY o.created_at DESC
        ");

    $stmt->execute([
        $user_id
    ]);

    $orders =
        $stmt->fetchAll();

} catch (PDOException $e) {

    $error =
        'Unable to load orders: ' .
        $e->getMessage();
}


/* =========================================================
   STATUS CLASS
   ========================================================= */

function order_status_class($status)
{
    $status =
        strtolower(
            trim(
                (string)$status
            )
        );


    $allowed = [
        'pending',
        'paid',
        'processing',
        'shipped',
        'delivered',
        'cancelled',
        'failed'
    ];


    if (
        in_array(
            $status,
            $allowed,
            true
        )
    ) {

        return $status;

    }


    return 'pending';
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
    My Orders - SmartCart
</title>


<link
    rel="stylesheet"
    href="/smartcart/assets/css/style.css"
>


<style>

/* =========================================================
   ORDERS PAGE
   SAME DESIGN AS INDEX
   ========================================================= */

* {
    box-sizing: border-box;
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


/* NAVBAR */

.orders-navbar {
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

.orders-nav-inner {
    width: 92%;

    max-width: 1200px;

    min-height: 70px;

    margin: auto;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;
}

.orders-logo {
    color: #111827;

    text-decoration: none;

    font-size: 22px;

    font-weight: 900;
}

.orders-logo span {
    color: #4f46e5;
}

.orders-links {
    display: flex;

    align-items: center;

    gap: 5px;
}

.orders-links a {
    padding:
        9px 11px;

    border-radius: 8px;

    color: #4b5563;

    text-decoration: none;

    font-size: 12px;

    font-weight: 600;
}

.orders-links a:hover,
.orders-links .active {
    background: #eef2ff;

    color: #4f46e5;
}

.orders-user {
    display: flex;

    align-items: center;

    gap: 6px;
}

.orders-user span {
    color: #4f46e5;

    font-size: 11px;
}

.orders-user a {
    color: #dc2626;

    font-size: 11px;

    font-weight: 700;

    text-decoration: none;
}


/* HERO */

.orders-hero {
    padding:
        45px 0 30px;

    background:
        linear-gradient(
            135deg,
            #eef2ff,
            #ffffff
        );

    border-bottom:
        1px solid #e5e7eb;
}

.orders-container {
    width: 92%;

    max-width: 1200px;

    margin: auto;
}

.orders-hero h1 {
    margin: 0;

    font-size: 36px;

    font-weight: 900;
}

.orders-hero p {
    margin:
        7px 0 0;

    color: #6b7280;

    font-size: 13px;
}


/* ERROR */

.orders-error {
    margin-top: 18px;

    padding: 12px 14px;

    border-radius: 9px;

    background: #fee2e2;

    color: #991b1b;

    font-size: 11px;
}


/* LIST */

.orders-list {
    padding:
        35px 0 60px;

    display: grid;

    gap: 16px;
}


/* ORDER CARD */

.order-card {
    padding: 21px;

    background: white;

    border:
        1px solid #e5e7eb;

    border-radius: 16px;

    box-shadow:
        0 5px 18px
        rgba(15,23,42,.04);

    transition: .2s;
}

.order-card:hover {
    transform:
        translateY(-3px);

    box-shadow:
        0 15px 30px
        rgba(15,23,42,.08);
}


/* TOP */

.order-top {
    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    margin-bottom: 20px;

    padding-bottom: 15px;

    border-bottom:
        1px solid #f1f5f9;
}

.order-number {
    color: #111827;

    font-size: 16px;

    font-weight: 900;
}

.order-date {
    margin-top: 4px;

    color: #9ca3af;

    font-size: 10px;
}


/* STATUS */

.order-status {
    display: inline-flex;

    align-items: center;

    padding:
        6px 10px;

    border-radius: 50px;

    background: #eef2ff;

    color: #4338ca;

    font-size: 9px;

    font-weight: 800;

    text-transform: uppercase;
}

.order-status.pending {
    background: #fef3c7;

    color: #92400e;
}

.order-status.paid {
    background: #dcfce7;

    color: #15803d;
}

.order-status.processing {
    background: #dbeafe;

    color: #1d4ed8;
}

.order-status.shipped {
    background: #ede9fe;

    color: #6d28d9;
}

.order-status.delivered {
    background: #dcfce7;

    color: #15803d;
}

.order-status.cancelled,
.order-status.failed {
    background: #fee2e2;

    color: #b91c1c;
}


/* DETAILS */

.order-details-grid {
    display: grid;

    grid-template-columns:
        repeat(3,1fr);

    gap: 16px;

    margin-bottom: 18px;
}

.order-detail {
    padding: 13px;

    border-radius: 10px;

    background: #f8fafc;
}

.order-detail-label {
    color: #9ca3af;

    font-size: 9px;

    text-transform: uppercase;

    font-weight: 700;
}

.order-detail-value {
    margin-top: 4px;

    color: #111827;

    font-size: 13px;

    font-weight: 700;
}


/* ADDRESS */

.order-address {
    margin-bottom: 18px;

    padding: 14px;

    background: #f8fafc;

    border-radius: 10px;

    color: #6b7280;

    font-size: 11px;

    line-height: 1.6;
}

.order-address strong {
    display: block;

    margin-bottom: 4px;

    color: #111827;
}


/* BUTTON */

.order-button {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-height: 40px;

    padding:
        0 16px;

    border-radius: 8px;

    background: #4f46e5;

    color: white;

    text-decoration: none;

    font-size: 11px;

    font-weight: 700;
}

.order-button:hover {
    background: #4338ca;
}


/* EMPTY */

.orders-empty {
    padding:
        75px 25px;

    text-align: center;

    background: white;

    border:
        1px dashed #d1d5db;

    border-radius: 16px;
}

.orders-empty-icon {
    width: 75px;

    height: 75px;

    margin:
        0 auto 15px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 22px;

    background: #eef2ff;

    font-size: 32px;
}

.orders-empty h2 {
    margin: 0 0 7px;

    font-size: 21px;
}

.orders-empty p {
    margin: 0 0 20px;

    color: #6b7280;

    font-size: 12px;
}

.orders-shop {
    display: inline-flex;

    padding:
        11px 18px;

    border-radius: 9px;

    background: #4f46e5;

    color: white;

    font-size: 11px;

    font-weight: 700;

    text-decoration: none;
}


/* FOOTER */

.orders-footer {
    padding:
        35px 0;

    background: #111827;

    color: #9ca3af;
}

.orders-footer-inner {
    width: 92%;

    max-width: 1200px;

    margin: auto;

    display: flex;

    align-items: center;

    justify-content: space-between;
}

.orders-footer strong {
    color: white;
}

.orders-footer p {
    margin: 5px 0 0;

    font-size: 11px;
}


/* RESPONSIVE */

@media(max-width:750px) {

    .orders-links {
        display: none;
    }

    .order-details-grid {
        grid-template-columns:
            1fr 1fr;
    }

}

@media(max-width:500px) {

    .order-details-grid {
        grid-template-columns:
            1fr;
    }

    .orders-user span {
        display: none;
    }

    .order-top {
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

<nav class="orders-navbar">

<div class="orders-nav-inner">


<a
    href="/smartcart/index.php"
    class="orders-logo"
>
    🛒 Smart<span>Cart</span>
</a>


<div class="orders-links">

<a href="/smartcart/index.php">
    Home
</a>

<a href="/smartcart/products.php">
    Products
</a>

<a href="/smartcart/cart.php">
    🛒 Cart
</a>

<a href="/smartcart/wishlist.php">
    Wishlist
</a>

<a
    href="/smartcart/orders.php"
    class="active"
>
    📦 Orders
</a>


<?php if (
    (
        $_SESSION['user']['role']
        ?? ''
    ) === 'admin'
): ?>

<a href="/smartcart/admin/index.php">
    Admin
</a>

<?php endif; ?>

</div>


<div class="orders-user">

<span>
    Hi,
    <?= htmlspecialchars(
        $_SESSION['user']['name']
    ) ?>
</span>

<a href="/smartcart/logout.php">
    Logout
</a>

</div>


</div>

</nav>


<!-- =========================================================
     HERO
     ========================================================= -->

<section class="orders-hero">

<div class="orders-container">

<h1>
    📦 My Orders
</h1>

<p>
    Track your SmartCart purchases and order status.
</p>


<?php if ($error !== ''): ?>

<div class="orders-error">

<?= htmlspecialchars(
    $error
) ?>

</div>

<?php endif; ?>


</div>

</section>


<!-- =========================================================
     ORDERS
     ========================================================= -->

<main class="orders-container">


<div class="orders-list">


<?php if (
    empty($orders)
): ?>


<div class="orders-empty">

<div class="orders-empty-icon">
    📦
</div>

<h2>
    No Orders Yet
</h2>

<p>
    Your completed purchases will appear here.
</p>

<a
    href="/smartcart/products.php"
    class="orders-shop"
>
    🛍️ Start Shopping
</a>

</div>


<?php else: ?>


<?php foreach (
    $orders as $order
): ?>


<div class="order-card">


<div class="order-top">


<div>

<div class="order-number">

Order #<?= (int)$order['id'] ?>

</div>


<div class="order-date">

<?= htmlspecialchars(
    $order['created_at']
) ?>

</div>

</div>


<div
    class="order-status <?= htmlspecialchars(
        order_status_class(
            $order['status']
        )
    ) ?>"
>

<?= htmlspecialchars(
    $order['status']
) ?>

</div>


</div>


<div class="order-details-grid">


<div class="order-detail">

<div class="order-detail-label">
    Total Amount
</div>

<div class="order-detail-value">

₹<?= number_format(
    (float)$order['total_amount'],
    2
) ?>

</div>

</div>


<div class="order-detail">

<div class="order-detail-label">
    Payment
</div>

<div class="order-detail-value">

<?= htmlspecialchars(
    $order['payment_method']
) ?>

</div>

</div>


<div class="order-detail">

<div class="order-detail-label">
    Status
</div>

<div class="order-detail-value">

<?= htmlspecialchars(
    ucfirst(
        $order['status']
    )
) ?>

</div>

</div>


</div>


<div class="order-address">

<strong>
    🚚 Shipping Address
</strong>

<?= nl2br(
    htmlspecialchars(
        $order['shipping_address']
    )
) ?>

</div>


<a
    href="/smartcart/order-details.php?id=<?= (int)$order['id'] ?>"
    class="order-button"
>
    View Order Details →
</a>


</div>


<?php endforeach; ?>


<?php endif; ?>


</div>


</main>


<!-- =========================================================
     FOOTER
     ========================================================= -->

<footer class="orders-footer">

<div class="orders-footer-inner">

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
    © <?= date('Y') ?> SmartCart
</p>

</div>

</div>

</footer>


</body>

</html>