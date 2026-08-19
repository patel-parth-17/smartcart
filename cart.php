<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';


/* =========================================================
   LOGIN CHECK
   ========================================================= */

if (!isset($_SESSION['user'])) {
    header('Location: /smartcart/login.php');
    exit;
}


$user_id = (int)$_SESSION['user']['id'];

$message = '';
$error = '';


/* =========================================================
   CART ACTIONS
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);


    if ($product_id > 0) {

        try {

            /*
            -----------------------------------------------------
            CHECK PRODUCT
            -----------------------------------------------------
            */

            $product_stmt = $pdo->prepare("
                SELECT
                    id,
                    name,
                    stock
                FROM products
                WHERE id = ?
                LIMIT 1
            ");

            $product_stmt->execute([
                $product_id
            ]);

            $product = $product_stmt->fetch();


            if (!$product) {

                $error = 'Product not found.';

            } else {


                /*
                -------------------------------------------------
                ADD
                -------------------------------------------------
                */

                if ($action === 'add') {

                    $quantity = max(1, $quantity);


                    $cart_stmt = $pdo->prepare("
                        SELECT quantity
                        FROM cart
                        WHERE user_id = ?
                        AND product_id = ?
                        LIMIT 1
                    ");

                    $cart_stmt->execute([
                        $user_id,
                        $product_id
                    ]);

                    $existing = $cart_stmt->fetch();


                    if ($existing) {

                        $new_quantity =
                            (int)$existing['quantity']
                            +
                            $quantity;


                        if (
                            $new_quantity >
                            (int)$product['stock']
                        ) {

                            $error =
                                'Only ' .
                                (int)$product['stock'] .
                                ' item(s) available.';

                        } else {

                            $update_stmt = $pdo->prepare("
                                UPDATE cart
                                SET quantity = ?
                                WHERE user_id = ?
                                AND product_id = ?
                            ");

                            $update_stmt->execute([
                                $new_quantity,
                                $user_id,
                                $product_id
                            ]);

                            $message =
                                'Product added to cart.';

                        }

                    } else {

                        if (
                            $quantity >
                            (int)$product['stock']
                        ) {

                            $error =
                                'Only ' .
                                (int)$product['stock'] .
                                ' item(s) available.';

                        } else {

                            $insert_stmt = $pdo->prepare("
                                INSERT INTO cart
                                (
                                    user_id,
                                    product_id,
                                    quantity
                                )
                                VALUES
                                (
                                    ?,
                                    ?,
                                    ?
                                )
                            ");

                            $insert_stmt->execute([
                                $user_id,
                                $product_id,
                                $quantity
                            ]);

                            $message =
                                'Product added to cart.';

                        }
                    }
                }


                /*
                -------------------------------------------------
                UPDATE
                -------------------------------------------------
                */

                elseif ($action === 'update') {

                    if ($quantity <= 0) {

                        $delete_stmt = $pdo->prepare("
                            DELETE FROM cart
                            WHERE user_id = ?
                            AND product_id = ?
                        ");

                        $delete_stmt->execute([
                            $user_id,
                            $product_id
                        ]);

                        $message =
                            'Product removed from cart.';

                    } else {

                        if (
                            $quantity >
                            (int)$product['stock']
                        ) {

                            $error =
                                'Only ' .
                                (int)$product['stock'] .
                                ' item(s) available.';

                        } else {

                            $update_stmt = $pdo->prepare("
                                UPDATE cart
                                SET quantity = ?
                                WHERE user_id = ?
                                AND product_id = ?
                            ");

                            $update_stmt->execute([
                                $quantity,
                                $user_id,
                                $product_id
                            ]);

                            $message =
                                'Cart updated.';

                        }
                    }
                }


                /*
                -------------------------------------------------
                REMOVE
                -------------------------------------------------
                */

                elseif ($action === 'remove') {

                    $delete_stmt = $pdo->prepare("
                        DELETE FROM cart
                        WHERE user_id = ?
                        AND product_id = ?
                    ");

                    $delete_stmt->execute([
                        $user_id,
                        $product_id
                    ]);

                    $message =
                        'Product removed from cart.';
                }
            }


        } catch (PDOException $e) {

            $error =
                'Database error: ' .
                $e->getMessage();
        }
    }
}


/* =========================================================
   GET CART
   ========================================================= */

$cart = [];

try {

    $stmt = $pdo->prepare("
        SELECT

            c.product_id,
            c.quantity,

            p.name,
            p.price,
            p.stock,
            p.image,
            p.brand,

            c2.name AS category_name

        FROM cart c

        INNER JOIN products p
            ON c.product_id = p.id

        LEFT JOIN categories c2
            ON p.category_id = c2.id

        WHERE c.user_id = ?

        ORDER BY c.id DESC
    ");

    $stmt->execute([
        $user_id
    ]);

    $cart = $stmt->fetchAll();

} catch (PDOException $e) {

    $error =
        'Unable to load cart: ' .
        $e->getMessage();
}


/* =========================================================
   IMAGE FUNCTION
   ========================================================= */

function cart_product_image($image)
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


/* =========================================================
   TOTAL
   ========================================================= */

$subtotal = 0;

foreach ($cart as $item) {

    $subtotal +=
        (float)$item['price']
        *
        (int)$item['quantity'];
}

$delivery = 0;

$total = $subtotal + $delivery;

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
    My Cart - SmartCart
</title>

<link
    rel="stylesheet"
    href="/smartcart/assets/css/style.css"
>

<style>

/* =========================================================
   CART PAGE - SAME INDEX DESIGN
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

.cart-navbar {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 3px 15px rgba(0,0,0,.05);
    position: sticky;
    top: 0;
    z-index: 9999;
}

.cart-nav-inner {
    width: 92%;
    max-width: 1200px;
    min-height: 70px;
    margin: auto;

    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
}

.cart-logo {
    color: #111827;
    text-decoration: none;
    font-size: 22px;
    font-weight: 900;
}

.cart-logo span {
    color: #4f46e5;
}

.cart-nav-links {
    display: flex;
    align-items: center;
    gap: 5px;
}

.cart-nav-links a {
    padding: 9px 11px;
    border-radius: 8px;
    color: #4b5563;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
}

.cart-nav-links a:hover {
    background: #eef2ff;
    color: #4f46e5;
}

.cart-nav-links .active {
    background: #eef2ff;
    color: #4f46e5;
}

.cart-nav-user {
    display: flex;
    align-items: center;
    gap: 6px;
}

.cart-nav-user span {
    color: #4f46e5;
    font-size: 11px;
}

.cart-nav-user a {
    color: #dc2626;
    text-decoration: none;
    font-size: 11px;
    font-weight: 700;
}


/* PAGE HEADER */

.cart-hero {
    padding: 45px 0 30px;
    background:
        linear-gradient(
            135deg,
            #eef2ff,
            #ffffff
        );
    border-bottom: 1px solid #e5e7eb;
}

.cart-container {
    width: 92%;
    max-width: 1200px;
    margin: auto;
}

.cart-hero h1 {
    margin: 0;
    font-size: 36px;
    font-weight: 900;
}

.cart-hero p {
    margin: 7px 0 0;
    color: #6b7280;
    font-size: 13px;
}


/* ALERT */

.cart-alert {
    margin: 20px 0 0;
    padding: 12px 14px;
    border-radius: 9px;
    font-size: 12px;
}

.cart-success {
    background: #dcfce7;
    color: #166534;
}

.cart-error {
    background: #fee2e2;
    color: #991b1b;
}


/* LAYOUT */

.cart-layout {
    display: grid;
    grid-template-columns:
        minmax(0,1fr) 340px;
    gap: 20px;
    padding: 35px 0 60px;
}


/* CARD */

.cart-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    box-shadow:
        0 5px 18px rgba(15,23,42,.04);
}


/* ITEMS */

.cart-items {
    padding: 0 20px;
}

.cart-item {
    display: grid;
    grid-template-columns:
        90px minmax(0,1fr) auto;
    gap: 16px;
    align-items: center;
    padding: 20px 0;
    border-bottom: 1px solid #f0f2f5;
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-image {
    width: 90px;
    height: 90px;
    overflow: hidden;
    border-radius: 12px;
    background:
        linear-gradient(
            135deg,
            #f8fafc,
            #eef2ff
        );
}

.cart-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 7px;
}

.cart-info h3 {
    margin: 0 0 5px;
    font-size: 15px;
}

.cart-brand {
    display: block;
    margin-bottom: 4px;
    color: #6366f1;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
}

.cart-category {
    display: inline-block;
    margin-bottom: 6px;
    padding: 3px 7px;
    background: #f3f4f6;
    border-radius: 50px;
    color: #6b7280;
    font-size: 9px;
}

.cart-info p {
    margin: 0;
    color: #6b7280;
    font-size: 11px;
}

.cart-right {
    text-align: right;
}

.cart-price {
    color: #111827;
    font-size: 17px;
    font-weight: 900;
    margin-bottom: 8px;
}


/* QUANTITY */

.quantity {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 8px;
}

.quantity button {
    width: 29px;
    height: 29px;
    border: 1px solid #dbe1ea;
    border-radius: 7px;
    background: white;
    color: #4338ca;
    cursor: pointer;
    font-weight: 700;
}

.quantity button:hover {
    background: #eef2ff;
}

.quantity span {
    width: 25px;
    text-align: center;
    font-size: 12px;
    font-weight: 700;
}


/* REMOVE */

.remove-btn {
    display: block;
    margin-left: auto;
    border: none;
    background: transparent;
    color: #dc2626;
    cursor: pointer;
    font-size: 10px;
    font-weight: 700;
}

.remove-btn:hover {
    text-decoration: underline;
}


/* SUMMARY */

.summary {
    padding: 24px;
    position: sticky;
    top: 90px;
}

.summary h2 {
    margin: 0 0 20px;
    font-size: 19px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 9px 0;
    color: #6b7280;
    font-size: 12px;
}

.summary-row strong {
    color: #111827;
}

.summary-total {
    display: flex;
    justify-content: space-between;
    margin-top: 10px;
    padding-top: 16px;
    border-top: 1px solid #e5e7eb;
    color: #111827;
    font-size: 21px;
    font-weight: 900;
}

.checkout-btn {
    width: 100%;
    margin-top: 18px;
    padding: 12px;
    border: none;
    border-radius: 9px;
    background: #4f46e5;
    color: white;
    text-decoration: none;
    text-align: center;
    font-size: 12px;
    font-weight: 700;
    display: block;
}

.checkout-btn:hover {
    background: #4338ca;
}

.continue-btn {
    width: 100%;
    margin-top: 8px;
    padding: 11px;
    border-radius: 9px;
    background: #eef2ff;
    color: #4338ca;
    text-decoration: none;
    text-align: center;
    display: block;
    font-size: 11px;
    font-weight: 700;
}


/* EMPTY */

.cart-empty {
    padding: 75px 25px;
    text-align: center;
}

.cart-empty-icon {
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

.cart-empty h2 {
    margin: 0 0 7px;
    font-size: 21px;
}

.cart-empty p {
    margin: 0 0 20px;
    color: #6b7280;
    font-size: 12px;
}

.shop-btn {
    display: inline-flex;
    padding: 11px 17px;
    border-radius: 9px;
    background: #4f46e5;
    color: white;
    text-decoration: none;
    font-size: 11px;
    font-weight: 700;
}


/* FOOTER */

.cart-footer {
    margin-top: 20px;
    padding: 35px 0;
    background: #111827;
    color: #9ca3af;
}

.cart-footer-inner {
    width: 92%;
    max-width: 1200px;
    margin: auto;

    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cart-footer strong {
    color: white;
}

.cart-footer p {
    margin: 5px 0 0;
    font-size: 11px;
}


/* RESPONSIVE */

@media(max-width:900px) {

    .cart-layout {
        grid-template-columns: 1fr;
    }

    .summary {
        position: static;
    }

}

@media(max-width:700px) {

    .cart-nav-links {
        display: none;
    }

    .cart-item {
        grid-template-columns:
            70px minmax(0,1fr);
    }

    .cart-image {
        width: 70px;
        height: 70px;
    }

    .cart-right {
        grid-column: 2;
        text-align: left;
    }

    .remove-btn {
        margin-left: 0;
    }

}

</style>

</head>

<body>


<!-- =========================================================
     NAVBAR
     ========================================================= -->

<nav class="cart-navbar">

<div class="cart-nav-inner">

<a
    href="/smartcart/index.php"
    class="cart-logo"
>
    🛒 Smart<span>Cart</span>
</a>


<div class="cart-nav-links">

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

<a
    href="/smartcart/cart.php"
    class="active"
>
    🛒 Cart
</a>

<a
    href="/smartcart/wishlist.php"
>
    Wishlist
</a>

<a
    href="/smartcart/orders.php"
>
    Orders
</a>


<?php if (
    ($_SESSION['user']['role'] ?? '') === 'admin'
): ?>

<a
    href="/smartcart/admin/index.php"
>
    Admin
</a>

<?php endif; ?>

</div>


<div class="cart-nav-user">

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


<!-- =========================================================
     HEADER
     ========================================================= -->

<section class="cart-hero">

<div class="cart-container">

<h1>
    🛒 My Shopping Cart
</h1>

<p>
    Review your selected products before checkout.
</p>


<?php if ($message !== ''): ?>

<div class="cart-alert cart-success">
    <?= htmlspecialchars($message) ?>
</div>

<?php endif; ?>


<?php if ($error !== ''): ?>

<div class="cart-alert cart-error">
    <?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>

</div>

</section>


<!-- =========================================================
     CART CONTENT
     ========================================================= -->

<main class="cart-container">


<?php if (empty($cart)): ?>


<div class="cart-card cart-empty">

<div class="cart-empty-icon">
    🛒
</div>

<h2>
    Your cart is empty
</h2>

<p>
    Discover something you love and add it to your cart.
</p>

<a
    href="/smartcart/products.php"
    class="shop-btn"
>
    🛍️ Start Shopping
</a>

</div>


<?php else: ?>


<div class="cart-layout">


<!-- ITEMS -->

<div class="cart-card cart-items">


<?php foreach ($cart as $item): ?>


<div class="cart-item">


<div class="cart-image">

<a
    href="/smartcart/product.php?id=<?= (int)$item['product_id'] ?>"
>

<img
    src="<?= htmlspecialchars(
        cart_product_image(
            $item['image']
        )
    ) ?>"
    alt="<?= htmlspecialchars(
        $item['name']
    ) ?>"
    onerror="
        this.onerror=null;
        this.src='/smartcart/assets/images/no-image.png';
    "
>

</a>

</div>


<div class="cart-info">


<?php if (
    !empty($item['brand'])
): ?>

<span class="cart-brand">

<?= htmlspecialchars(
    $item['brand']
) ?>

</span>

<?php endif; ?>


<?php if (
    !empty($item['category_name'])
): ?>

<span class="cart-category">

<?= htmlspecialchars(
    $item['category_name']
) ?>

</span>

<?php endif; ?>


<h3>

<?= htmlspecialchars(
    $item['name']
) ?>

</h3>


<p>

₹<?= number_format(
    (float)$item['price'],
    2
) ?>

each

</p>


</div>


<div class="cart-right">


<div class="cart-price">

₹<?= number_format(
    (float)$item['price']
    *
    (int)$item['quantity'],
    2
) ?>

</div>


<form
    method="POST"
    class="quantity"
>

<input
    type="hidden"
    name="action"
    value="update"
>

<input
    type="hidden"
    name="product_id"
    value="<?= (int)$item['product_id'] ?>"
>


<button
    type="submit"
    name="quantity"
    value="<?= max(
        1,
        (int)$item['quantity'] - 1
    ) ?>"
>
    −
</button>


<span>
    <?= (int)$item['quantity'] ?>
</span>


<button
    type="submit"
    name="quantity"
    value="<?= (int)$item['quantity'] + 1 ?>"
>
    +
</button>

</form>


<form
    method="POST"
>

<input
    type="hidden"
    name="action"
    value="remove"
>

<input
    type="hidden"
    name="product_id"
    value="<?= (int)$item['product_id'] ?>"
>

<button
    type="submit"
    class="remove-btn"
>
    Remove
</button>

</form>


</div>


</div>


<?php endforeach; ?>


</div>


<!-- SUMMARY -->

<aside
    class="cart-card summary"
>

<h2>
    Order Summary
</h2>


<div class="summary-row">

<span>
    Subtotal
</span>

<strong>

₹<?= number_format(
    $subtotal,
    2
) ?>

</strong>

</div>


<div class="summary-row">

<span>
    Delivery
</span>

<strong>
    Free
</strong>

</div>


<div class="summary-total">

<span>
    Total
</span>

<span>

₹<?= number_format(
    $total,
    2
) ?>

</span>

</div>


<a
    href="/smartcart/checkout.php"
    class="checkout-btn"
>
    Proceed to Checkout
</a>


<a
    href="/smartcart/products.php"
    class="continue-btn"
>
    Continue Shopping
</a>


</aside>


</div>


<?php endif; ?>


</main>


<!-- FOOTER -->

<footer class="cart-footer">

<div class="cart-footer-inner">

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