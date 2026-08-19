<?php

require_once __DIR__ . '/auth/auth_check.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$user_id =
    (int)$_SESSION['user']['id'];

$order_id =
    (int)($_GET['id'] ?? 0);


if ($order_id <= 0) {

    header(
        'Location: /smartcart/orders.php'
    );

    exit;
}


$stmt =
    $pdo->prepare("
        SELECT *
        FROM orders
        WHERE id = ?
        AND user_id = ?
        LIMIT 1
    ");

$stmt->execute([
    $order_id,
    $user_id
]);


$order =
    $stmt->fetch();


if (!$order) {

    die(
        'Order not found.'
    );

}


$stmt =
    $pdo->prepare("
        SELECT
            oi.quantity,
            oi.price,

            p.id AS product_id,
            p.name,
            p.image

        FROM order_items oi

        INNER JOIN products p
            ON oi.product_id = p.id

        WHERE oi.order_id = ?
    ");

$stmt->execute([
    $order_id
]);

$items =
    $stmt->fetchAll();

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
    Order #<?= (int)$order['id'] ?> - SmartCart
</title>

<link
    rel="stylesheet"
    href="/smartcart/assets/css/style.css"
>

<style>

.order-page {
    width:92%;
    max-width:1000px;
    margin:40px auto;
}

.order-box {
    background:white;
    border:1px solid #e5e7eb;
    border-radius:15px;
    padding:22px;
    margin-bottom:18px;
}

.order-header {
    display:flex;
    justify-content:space-between;
    gap:15px;
}

.order-status {
    padding:6px 10px;
    border-radius:50px;
    background:#eef2ff;
    color:#4338ca;
    font-size:10px;
    font-weight:700;
}

.order-item {
    display:flex;
    align-items:center;
    gap:14px;
    padding:14px 0;
    border-bottom:1px solid #f1f5f9;
}

.order-item img {
    width:70px;
    height:70px;
    object-fit:contain;
    border-radius:9px;
    background:#f3f4f6;
}

.order-item-info {
    flex:1;
}

.order-item-info h3 {
    font-size:13px;
}

.order-meta {
    color:#6b7280;
    font-size:11px;
}

</style>

</head>

<body>


<?php
include __DIR__ . '/includes/navbar.php';
?>


<main class="order-page">


<div class="order-box">

<div class="order-header">

<div>

<h1>
    Order #<?= (int)$order['id'] ?>
</h1>

<p style="color:#6b7280;font-size:12px;">
    <?= e(
        $order['created_at']
    ) ?>
</p>

</div>


<div class="order-status">

<?= e(
    $order['status']
) ?>

</div>

</div>

</div>


<div class="order-box">

<h2>
    📦 Items
</h2>


<?php foreach (
    $items as $item
): ?>


<div class="order-item">


<img
    src="<?= e(
        product_image(
            $item['image']
        )
    ) ?>"
    alt="<?= e(
        $item['name']
    ) ?>"
>


<div class="order-item-info">

<h3>
    <?= e(
        $item['name']
    ) ?>
</h3>

<div class="order-meta">

Quantity:
<?= (int)$item['quantity'] ?>

<br>

Price:
<?= currency(
    $item['price']
) ?>

</div>

</div>


<strong>

<?= currency(
    $item['price']
    *
    $item['quantity']
) ?>

</strong>


</div>


<?php endforeach; ?>


<div
    style="
        display:flex;
        justify-content:space-between;
        margin-top:20px;
        font-size:20px;
        font-weight:900;
    "
>

<span>
    Total
</span>

<span>
    <?= currency(
        $order['total_amount']
    ) ?>
</span>

</div>


</div>


<div class="order-box">

<h2>
    🚚 Shipping
</h2>

<p style="color:#6b7280;line-height:1.7;">

<?= nl2br(
    e(
        $order['shipping_address']
    )
) ?>

</p>


<p>

<strong>
Payment:
</strong>

<?= e(
    $order['payment_method']
) ?>

</p>


</div>


<a
    href="/smartcart/orders.php"
    class="btn btn-primary"
>
    ← Back to Orders
</a>


</main>


<?php
include __DIR__ . '/includes/footer.php';
?>


</body>
</html>