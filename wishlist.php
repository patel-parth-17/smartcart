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

$message = '';
$error = '';


/* =========================================================
   WISHLIST ACTION
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action =
        $_POST['action'] ?? '';

    $product_id =
        (int)(
            $_POST['product_id']
            ?? 0
        );


    if ($product_id > 0) {

        try {

            if ($action === 'add') {

                $stmt =
                    $pdo->prepare("
                        INSERT IGNORE INTO wishlist
                        (
                            user_id,
                            product_id,
                            created_at
                        )
                        VALUES
                        (
                            ?,
                            ?,
                            NOW()
                        )
                    ");

                $stmt->execute([
                    $user_id,
                    $product_id
                ]);

                $message =
                    'Product added to wishlist.';

            }


            elseif (
                $action === 'remove'
            ) {

                $stmt =
                    $pdo->prepare("
                        DELETE FROM wishlist

                        WHERE user_id = ?

                        AND product_id = ?
                    ");

                $stmt->execute([
                    $user_id,
                    $product_id
                ]);

                $message =
                    'Product removed from wishlist.';
            }


        } catch (PDOException $e) {

            $error =
                'Database error: ' .
                $e->getMessage();
        }
    }
}


/* =========================================================
   GET WISHLIST
   ========================================================= */

$wishlist = [];

try {

    $stmt =
        $pdo->prepare("
            SELECT

                w.product_id,

                p.name,
                p.description,
                p.price,
                p.stock,
                p.image,
                p.brand,
                p.rating,

                c.name AS category_name

            FROM wishlist w

            INNER JOIN products p
                ON w.product_id = p.id

            LEFT JOIN categories c
                ON p.category_id = c.id

            WHERE w.user_id = ?

            ORDER BY w.created_at DESC
        ");

    $stmt->execute([
        $user_id
    ]);

    $wishlist =
        $stmt->fetchAll();

} catch (PDOException $e) {

    $error =
        'Unable to load wishlist: ' .
        $e->getMessage();
}


/* =========================================================
   IMAGE
   ========================================================= */

function wishlist_product_image($image)
{
    $image = trim((string)$image);

    if ($image === '') {
        return '/smartcart/assets/images/no-image.png';
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
        return '/smartcart/' . $image;
    }

    return '/smartcart/uploads/' .
        ltrim($image, '/');
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
    My Wishlist - SmartCart
</title>

<link
    rel="stylesheet"
    href="/smartcart/assets/css/style.css"
>

<style>

/* =========================================================
   WISHLIST PAGE
   ========================================================= */

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: #f5f7fb;
    color: #111827;
    font-family: Arial, Helvetica, sans-serif;
}


/* NAVBAR */

.wish-navbar {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 3px 15px rgba(0,0,0,.05);
    position: sticky;
    top: 0;
    z-index: 9999;
}

.wish-nav-inner {
    width: 92%;
    max-width: 1200px;
    min-height: 70px;
    margin: auto;

    display: flex;
    align-items: center;
    justify-content: space-between;
}

.wish-logo {
    color: #111827;
    font-size: 22px;
    font-weight: 900;
    text-decoration: none;
}

.wish-logo span {
    color: #4f46e5;
}

.wish-links {
    display: flex;
    align-items: center;
    gap: 5px;
}

.wish-links a {
    padding: 9px 11px;
    border-radius: 8px;
    text-decoration: none;
    color: #4b5563;
    font-size: 12px;
    font-weight: 600;
}

.wish-links a:hover,
.wish-links .active {
    background: #eef2ff;
    color: #4f46e5;
}

.wish-user {
    display: flex;
    align-items: center;
    gap: 6px;
}

.wish-user span {
    color: #4f46e5;
    font-size: 11px;
}

.wish-user a {
    color: #dc2626;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
}


/* HEADER */

.wish-hero {
    padding: 45px 0 30px;

    background:
        linear-gradient(
            135deg,
            #eef2ff,
            #ffffff
        );

    border-bottom: 1px solid #e5e7eb;
}

.wish-container {
    width: 92%;
    max-width: 1200px;
    margin: auto;
}

.wish-hero h1 {
    margin: 0;
    font-size: 36px;
    font-weight: 900;
}

.wish-hero p {
    margin: 7px 0 0;
    color: #6b7280;
    font-size: 13px;
}


/* ALERT */

.wish-alert {
    margin-top: 18px;
    padding: 11px 13px;
    border-radius: 8px;
    font-size: 11px;
}

.wish-success {
    background: #dcfce7;
    color: #166534;
}

.wish-error {
    background: #fee2e2;
    color: #991b1b;
}


/* GRID */

.wish-grid {
    padding: 35px 0 60px;

    display: grid;

    grid-template-columns:
        repeat(4,minmax(0,1fr));

    gap: 18px;
}


/* CARD */

.wish-card {
    overflow: hidden;

    background: white;

    border: 1px solid #e5e7eb;

    border-radius: 15px;

    box-shadow:
        0 5px 18px
        rgba(15,23,42,.04);

    transition: .2s;
}

.wish-card:hover {
    transform: translateY(-5px);

    box-shadow:
        0 18px 35px
        rgba(15,23,42,.09);
}


/* IMAGE */

.wish-image {
    height: 220px;

    position: relative;

    overflow: hidden;

    background:
        linear-gradient(
            135deg,
            #f8fafc,
            #eef2ff
        );
}

.wish-image img {
    width: 100%;
    height: 100%;

    display: block;

    object-fit: contain;

    padding: 12px;

    transition: .3s;
}

.wish-card:hover
.wish-image img {
    transform: scale(1.06);
}


/* HEART */

.wish-heart {
    position: absolute;

    right: 10px;
    top: 10px;

    width: 35px;
    height: 35px;

    display: flex;

    align-items: center;

    justify-content: center;

    border: none;

    border-radius: 50%;

    background: white;

    color: #ef4444;

    box-shadow:
        0 4px 12px
        rgba(0,0,0,.12);

    font-size: 17px;

    z-index: 3;
}


/* STOCK */

.wish-stock {
    position: absolute;

    left: 10px;
    top: 10px;

    z-index: 3;

    padding: 5px 8px;

    border-radius: 50px;

    background: #dcfce7;
    color: #15803d;

    font-size: 9px;
    font-weight: 700;
}

.wish-stock.out {
    background: #fee2e2;
    color: #b91c1c;
}


/* BODY */

.wish-body {
    padding: 16px;
}

.wish-brand {
    display: block;

    margin-bottom: 5px;

    color: #6366f1;

    font-size: 9px;

    font-weight: 700;

    text-transform: uppercase;
}

.wish-category {
    display: inline-block;

    margin-bottom: 7px;

    padding: 3px 7px;

    border-radius: 50px;

    background: #f3f4f6;

    color: #6b7280;

    font-size: 9px;
}

.wish-body h3 {
    min-height: 41px;

    margin: 0 0 7px;

    color: #111827;

    font-size: 14px;

    line-height: 1.4;
}

.wish-rating {
    margin-bottom: 7px;

    color: #f59e0b;

    font-size: 10px;
}

.wish-price {
    margin-bottom: 13px;

    color: #111827;

    font-size: 19px;

    font-weight: 900;
}


/* BUTTONS */

.wish-actions {
    display: grid;

    gap: 7px;
}

.wish-actions a,
.wish-actions button {
    width: 100%;

    height: 39px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 8px;

    border: none;

    font-size: 10px;

    font-weight: 700;

    cursor: pointer;

    text-decoration: none;
}

.wish-view {
    background: #4f46e5;

    color: white;
}

.wish-view:hover {
    background: #4338ca;
}

.wish-remove {
    background: #fee2e2;

    color: #b91c1c;
}

.wish-remove:hover {
    background: #fecaca;
}


/* EMPTY */

.wish-empty {
    grid-column: 1 / -1;

    padding: 75px 25px;

    background: white;

    border: 1px dashed #d1d5db;

    border-radius: 16px;

    text-align: center;
}

.wish-empty-icon {
    width: 75px;
    height: 75px;

    margin: 0 auto 15px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 22px;

    background: #eef2ff;

    font-size: 33px;
}

.wish-empty h2 {
    margin: 0 0 7px;
}

.wish-empty p {
    margin: 0 0 20px;

    color: #6b7280;

    font-size: 12px;
}

.wish-shop {
    display: inline-flex;

    padding: 11px 18px;

    border-radius: 9px;

    background: #4f46e5;

    color: white;

    font-size: 11px;

    font-weight: 700;

    text-decoration: none;
}


/* FOOTER */

.wish-footer {
    padding: 35px 0;

    background: #111827;

    color: #9ca3af;
}

.wish-footer-inner {
    width: 92%;
    max-width: 1200px;
    margin: auto;

    display: flex;
    justify-content: space-between;
}

.wish-footer strong {
    color: white;
}

.wish-footer p {
    margin: 5px 0 0;
    font-size: 11px;
}


/* RESPONSIVE */

@media(max-width:1000px) {

    .wish-grid {
        grid-template-columns:
            repeat(3,1fr);
    }

}

@media(max-width:750px) {

    .wish-links {
        display: none;
    }

    .wish-grid {
        grid-template-columns:
            repeat(2,1fr);
    }

}

@media(max-width:500px) {

    .wish-grid {
        grid-template-columns:1fr;
    }

    .wish-image {
        height:260px;
    }

    .wish-user span {
        display:none;
    }

}

</style>

</head>

<body>


<!-- NAVBAR -->

<nav class="wish-navbar">

<div class="wish-nav-inner">

<a
    href="/smartcart/index.php"
    class="wish-logo"
>
    🛒 Smart<span>Cart</span>
</a>


<div class="wish-links">

<a href="/smartcart/index.php">
    Home
</a>

<a href="/smartcart/products.php">
    Products
</a>

<a href="/smartcart/cart.php">
    🛒 Cart
</a>

<a
    href="/smartcart/wishlist.php"
    class="active"
>
    ♡ Wishlist
</a>

<a href="/smartcart/orders.php">
    Orders
</a>


<?php if (
    ($_SESSION['user']['role'] ?? '') === 'admin'
): ?>

<a href="/smartcart/admin/index.php">
    Admin
</a>

<?php endif; ?>

</div>


<div class="wish-user">

<span>
    Hi,
    <?= htmlspecialchars(
        $_SESSION['user']['name']
    ) ?>
</span>

<a
    href="/smartcart/logout.php"
>
    Logout
</a>

</div>

</div>

</nav>


<!-- HEADER -->

<section class="wish-hero">

<div class="wish-container">

<h1>
    ♡ My Wishlist
</h1>

<p>
    Save your favourite products and come back to them anytime.
</p>


<?php if ($message !== ''): ?>

<div class="wish-alert wish-success">
    <?= htmlspecialchars($message) ?>
</div>

<?php endif; ?>


<?php if ($error !== ''): ?>

<div class="wish-alert wish-error">
    <?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>


</div>

</section>


<!-- PRODUCTS -->

<main class="wish-container">

<div class="wish-grid">


<?php if (empty($wishlist)): ?>


<div class="wish-empty">

<div class="wish-empty-icon">
    ♡
</div>

<h2>
    Your Wishlist is Empty
</h2>

<p>
    Save products you love and find them here later.
</p>

<a
    href="/smartcart/products.php"
    class="wish-shop"
>
    🛍️ Explore Products
</a>

</div>


<?php else: ?>


<?php foreach (
    $wishlist as $product
): ?>


<div class="wish-card">


<div class="wish-image">


<?php if (
    (int)$product['stock'] > 0
): ?>

<span class="wish-stock">
    ✓ In Stock
</span>

<?php else: ?>

<span class="wish-stock out">
    Out of Stock
</span>

<?php endif; ?>


<div class="wish-heart">
    ♥
</div>


<a
    href="/smartcart/product.php?id=<?= (int)$product['product_id'] ?>"
>

<img
    src="<?= htmlspecialchars(
        wishlist_product_image(
            $product['image']
        )
    ) ?>"
    alt="<?= htmlspecialchars(
        $product['name']
    ) ?>"
    onerror="
        this.onerror=null;
        this.src='/smartcart/assets/images/no-image.png';
    "
>

</a>


</div>


<div class="wish-body">


<?php if (
    !empty($product['brand'])
): ?>

<span class="wish-brand">

<?= htmlspecialchars(
    $product['brand']
) ?>

</span>

<?php endif; ?>


<?php if (
    !empty(
        $product['category_name']
    )
): ?>

<span class="wish-category">

<?= htmlspecialchars(
    $product['category_name']
) ?>

</span>

<?php endif; ?>


<h3>

<?= htmlspecialchars(
    $product['name']
) ?>

</h3>


<div class="wish-rating">

⭐

<?= number_format(
    (float)(
        $product['rating']
        ?? 0
    ),
    1
) ?>/5

</div>


<div class="wish-price">

₹<?= number_format(
    (float)$product['price'],
    2
) ?>

</div>


<div class="wish-actions">


<a
    href="/smartcart/product.php?id=<?= (int)$product['product_id'] ?>"
    class="wish-view"
>
    View Product
</a>


<form method="POST">

<input
    type="hidden"
    name="action"
    value="remove"
>

<input
    type="hidden"
    name="product_id"
    value="<?= (int)$product['product_id'] ?>"
>

<button
    type="submit"
    class="wish-remove"
>
    Remove from Wishlist
</button>

</form>


</div>


</div>


</div>


<?php endforeach; ?>


<?php endif; ?>


</div>

</main>


<!-- FOOTER -->

<footer class="wish-footer">

<div class="wish-footer-inner">

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