<?php

session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$id =
    (int)($_GET['id'] ?? 0);


if ($id <= 0) {

    header(
        'Location: /smartcart/products.php'
    );

    exit;
}


$stmt =
    $pdo->prepare("
        SELECT
            p.*,
            c.name AS category_name

        FROM products p

        LEFT JOIN categories c
            ON p.category_id = c.id

        WHERE p.id = ?

        LIMIT 1
    ");

$stmt->execute([
    $id
]);


$product =
    $stmt->fetch();


if (!$product) {

    die('Product not found.');

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
    <?= e($product['name']) ?> - SmartCart
</title>

<link
    rel="stylesheet"
    href="/smartcart/assets/css/style.css"
>

<style>

.product-detail {
    width:92%;
    max-width:1100px;
    margin:45px auto;
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:35px;
}

.product-detail-image {
    height:500px;
    background:#f3f4f6;
    border-radius:18px;
    overflow:hidden;
}

.product-detail-image img {
    width:100%;
    height:100%;
    object-fit:contain;
    padding:25px;
}

.product-detail-info {
    background:white;
    border:1px solid #e5e7eb;
    border-radius:18px;
    padding:30px;
}

.product-detail-info h1 {
    font-size:32px;
    margin-bottom:10px;
}

.detail-brand {
    color:#6366f1;
    font-size:12px;
    font-weight:700;
    margin-bottom:10px;
}

.detail-category {
    color:#6b7280;
    font-size:12px;
    margin-bottom:15px;
}

.detail-rating {
    color:#f59e0b;
    margin-bottom:15px;
}

.detail-price {
    font-size:30px;
    font-weight:900;
    margin-bottom:15px;
}

.detail-description {
    color:#6b7280;
    font-size:14px;
    line-height:1.7;
    margin-bottom:20px;
}

.detail-stock {
    margin-bottom:20px;
    font-size:12px;
    color:#15803d;
    font-weight:700;
}

.detail-actions {
    display:flex;
    gap:10px;
}

.detail-actions button {
    flex:1;
}

@media(max-width:750px) {
    .product-detail {
        grid-template-columns:1fr;
    }

    .product-detail-image {
        height:350px;
    }
}

</style>

</head>

<body>


<?php
include __DIR__ . '/includes/navbar.php';
?>


<main class="product-detail">


<div class="product-detail-image">

<img
    src="<?= e(
        product_image(
            $product['image']
        )
    ) ?>"
    alt="<?= e(
        $product['name']
    ) ?>"
    onerror="
        this.onerror=null;
        this.src='/smartcart/assets/images/no-image.png';
    "
>

</div>


<div class="product-detail-info">


<?php if (
    !empty($product['brand'])
): ?>

<div class="detail-brand">

<?= e(
    $product['brand']
) ?>

</div>

<?php endif; ?>


<h1>

<?= e(
    $product['name']
) ?>

</h1>


<div class="detail-category">

Category:
<?= e(
    $product['category_name']
    ?? 'Product'
) ?>

</div>


<div class="detail-rating">

⭐

<?= number_format(
    (float)(
        $product['rating']
        ?? 0
    ),
    1
) ?>/5

</div>


<div class="detail-price">

<?= currency(
    $product['price']
) ?>

</div>


<div class="detail-description">

<?= nl2br(
    e(
        $product['description']
    )
) ?>

</div>


<div class="detail-stock">

<?php if (
    (int)$product['stock'] > 0
): ?>

✓
<?= (int)$product['stock'] ?>
items available

<?php else: ?>

Out of stock

<?php endif; ?>

</div>


<div class="detail-actions">

<a
    href="/smartcart/products.php"
    class="btn"
>
    ← Continue Shopping
</a>


<?php if (
    (int)$product['stock'] > 0
): ?>

<?php if (
    is_logged_in()
): ?>

<button
    class="btn btn-primary"
    onclick="
        addProductToCart(
            <?= (int)$product['id'] ?>
        )
    "
>
    🛒 Add to Cart
</button>

<?php else: ?>

<a
    href="/smartcart/login.php"
    class="btn btn-primary"
>
    Login to Buy
</a>

<?php endif; ?>

<?php endif; ?>


</div>


</div>

</main>


<script>

async function addProductToCart(id)
{
    try {

        const response =
            await fetch(
                '/smartcart/api/cart.php',
                {
                    method:'POST',

                    headers:{
                        'Content-Type':
                            'application/x-www-form-urlencoded'
                    },

                    body:
                        'action=add' +
                        '&product_id=' +
                        encodeURIComponent(id) +
                        '&quantity=1'
                }
            );


        const data =
            await response.json();


        alert(
            data.message ||
            'Cart updated!'
        );


    } catch(error) {

        alert(
            'Unable to add product.'
        );

    }
}

</script>


<?php
include __DIR__ . '/includes/footer.php';
?>


</body>
</html>