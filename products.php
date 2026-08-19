<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/config/database.php';


/*
|--------------------------------------------------------------------------
| CHECK PDO
|--------------------------------------------------------------------------
*/

if (!isset($pdo) || !($pdo instanceof PDO)) {

    die(
        '<div style="font-family:Arial;padding:30px;margin:30px;background:#fee2e2;color:#991b1b;border:1px solid #fecaca;border-radius:10px;">
            <h2>Database Connection Error</h2>
            <p>$pdo was not created.</p>
            <p>Please check config/database.php</p>
        </div>'
    );
}


/*
|--------------------------------------------------------------------------
| FILTERS
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');

$category_id = (int)($_GET['category'] ?? 0);

$sort = $_GET['sort'] ?? 'newest';


/*
|--------------------------------------------------------------------------
| CATEGORIES
|--------------------------------------------------------------------------
*/

try {

    $category_stmt = $pdo->query("
        SELECT
            id,
            name,
            description
        FROM categories
        ORDER BY name ASC
    ");

    $categories = $category_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die(
        '<div style="font-family:Arial;padding:30px;color:#991b1b;">
            Category database error:
            ' . htmlspecialchars($e->getMessage()) . '
        </div>'
    );
}


/*
|--------------------------------------------------------------------------
| PRODUCT QUERY
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        p.id,
        p.category_id,
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
";

$where = [];
$params = [];


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

if ($search !== '') {

    $where[] = "
        (
            p.name LIKE :search
            OR p.description LIKE :search
            OR p.brand LIKE :search
            OR c.name LIKE :search
        )
    ";

    $params[':search'] = '%' . $search . '%';
}


/*
|--------------------------------------------------------------------------
| CATEGORY FILTER
|--------------------------------------------------------------------------
*/

if ($category_id > 0) {

    $where[] = "p.category_id = :category_id";

    $params[':category_id'] = $category_id;
}


/*
|--------------------------------------------------------------------------
| WHERE
|--------------------------------------------------------------------------
*/

if (!empty($where)) {

    $sql .= " WHERE " . implode(" AND ", $where);
}


/*
|--------------------------------------------------------------------------
| SORTING
|--------------------------------------------------------------------------
*/

switch ($sort) {

    case 'price_low':
        $sql .= " ORDER BY p.price ASC";
        break;

    case 'price_high':
        $sql .= " ORDER BY p.price DESC";
        break;

    case 'rating':
        $sql .= " ORDER BY p.rating DESC";
        break;

    case 'name':
        $sql .= " ORDER BY p.name ASC";
        break;

    default:
        $sql .= " ORDER BY p.created_at DESC";
        break;
}


/*
|--------------------------------------------------------------------------
| GET PRODUCTS
|--------------------------------------------------------------------------
*/

try {

    $product_stmt = $pdo->prepare($sql);

    foreach ($params as $key => $value) {

        if ($key === ':category_id') {

            $product_stmt->bindValue(
                $key,
                $value,
                PDO::PARAM_INT
            );

        } else {

            $product_stmt->bindValue(
                $key,
                $value,
                PDO::PARAM_STR
            );
        }
    }

    $product_stmt->execute();

    $products = $product_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    die(
        '<div style="font-family:Arial;padding:30px;color:#991b1b;">
            Product database error:
            ' . htmlspecialchars($e->getMessage()) . '
        </div>'
    );
}


/*
|--------------------------------------------------------------------------
| PRODUCT IMAGE
|--------------------------------------------------------------------------
*/

function smartcart_product_image($image)
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

    if (strpos($image, '/smartcart/') === 0) {
        return $image;
    }

    if (strpos($image, '/') === 0) {
        return $image;
    }

    if (strpos($image, 'uploads/') === 0) {
        return '/smartcart/' . $image;
    }

    return '/smartcart/uploads/' . ltrim($image, '/');
}


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

$is_logged_in = isset($_SESSION['user']);

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
        Products - SmartCart
    </title>

    <link
        rel="stylesheet"
        href="/smartcart/assets/css/style.css"
    >


    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f5f7fb;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
        }

        .products-page {
            min-height: 100vh;
        }


        /* NAVBAR */

        .products-navbar {
            width: 100%;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            position: sticky;
            top: 0;
            z-index: 9999;
        }

        .products-navbar-inner {
            width: 92%;
            max-width: 1200px;
            min-height: 70px;
            margin: auto;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .products-logo {
            color: #111827;
            text-decoration: none;
            font-size: 22px;
            font-weight: 900;
        }

        .products-logo span {
            color: #4f46e5;
        }

        .products-nav {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .products-nav a {
            padding: 9px 11px;
            border-radius: 8px;
            text-decoration: none;
            color: #4b5563;
            font-size: 12px;
            font-weight: 600;
        }

        .products-nav a:hover {
            background: #eef2ff;
            color: #4f46e5;
        }

        .products-login {
            background: #4f46e5 !important;
            color: white !important;
        }


        /* HEADER */

        .products-hero {
            padding: 45px 20px 35px;
            background:
                linear-gradient(
                    135deg,
                    #eef2ff,
                    #ffffff
                );
            border-bottom: 1px solid #e5e7eb;
        }

        .products-hero-inner {
            width: 92%;
            max-width: 1200px;
            margin: auto;
        }

        .products-hero h1 {
            margin: 0;
            color: #111827;
            font-size: 36px;
            font-weight: 900;
        }

        .products-hero p {
            margin: 8px 0 0;
            color: #6b7280;
            font-size: 13px;
        }


        /* FILTER */

        .filters {
            width: 92%;
            max-width: 1200px;
            margin: 25px auto;

            padding: 18px;

            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 14px;

            box-shadow:
                0 5px 18px
                rgba(0, 0, 0, 0.04);
        }

        .filter-form {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 10px;
        }

        .filter-form input,
        .filter-form select {
            width: 100%;
            height: 43px;

            padding: 0 12px;

            background: white;

            border: 1px solid #d1d5db;
            border-radius: 8px;

            outline: none;

            font-size: 12px;
        }

        .filter-form input:focus,
        .filter-form select:focus {
            border-color: #6366f1;

            box-shadow:
                0 0 0 3px
                rgba(99, 102, 241, 0.10);
        }

        .search-button {
            height: 43px;

            padding: 0 18px;

            border: none;
            border-radius: 8px;

            background: #4f46e5;

            color: white;

            font-size: 12px;
            font-weight: 700;

            cursor: pointer;
        }

        .search-button:hover {
            background: #4338ca;
        }

        .clear-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            height: 40px;

            margin-top: 10px;

            padding: 0 15px;

            border-radius: 8px;

            background: #f3f4f6;
            color: #374151;

            font-size: 11px;
            font-weight: 700;

            text-decoration: none;
        }


        /* PRODUCTS */

        .products-container {
            width: 92%;
            max-width: 1200px;

            margin: auto;

            padding-bottom: 60px;
        }

        .products-top {
            margin-bottom: 20px;

            color: #6b7280;

            font-size: 12px;
        }

        .products-top strong {
            color: #111827;
        }


        .shop-grid {
            display: grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap: 20px;
        }


        .shop-card {
            overflow: hidden;

            background: white;

            border: 1px solid #e5e7eb;
            border-radius: 15px;

            box-shadow:
                0 5px 18px
                rgba(15, 23, 42, 0.04);

            transition:
                transform .2s ease,
                box-shadow .2s ease;
        }

        .shop-card:hover {
            transform: translateY(-5px);

            box-shadow:
                0 18px 35px
                rgba(15, 23, 42, 0.09);
        }


        .shop-image {
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

        .shop-image img {
            width: 100%;
            height: 100%;

            display: block;

            object-fit: contain;

            padding: 12px;

            transition: transform .3s ease;
        }

        .shop-card:hover .shop-image img {
            transform: scale(1.06);
        }


        .stock-badge {
            position: absolute;

            top: 10px;
            left: 10px;

            z-index: 5;

            padding: 5px 8px;

            border-radius: 50px;

            background: #dcfce7;
            color: #15803d;

            font-size: 9px;
            font-weight: 700;
        }

        .stock-badge.out {
            background: #fee2e2;
            color: #b91c1c;
        }


        .shop-body {
            padding: 16px;
        }

        .shop-brand {
            display: block;

            margin-bottom: 5px;

            color: #6366f1;

            font-size: 10px;

            font-weight: 700;

            text-transform: uppercase;
        }

        .shop-category {
            display: inline-block;

            margin-bottom: 7px;

            padding: 3px 7px;

            border-radius: 50px;

            background: #f3f4f6;

            color: #6b7280;

            font-size: 9px;
        }

        .shop-name {
            min-height: 42px;

            margin: 0 0 7px;

            font-size: 15px;

            line-height: 1.4;
        }

        .shop-name a {
            color: #111827;

            text-decoration: none;
        }

        .shop-name a:hover {
            color: #4f46e5;
        }

        .shop-description {
            min-height: 36px;

            margin-bottom: 9px;

            color: #6b7280;

            font-size: 10px;

            line-height: 1.5;

            overflow: hidden;
        }

        .shop-rating {
            margin-bottom: 8px;

            color: #f59e0b;

            font-size: 11px;
        }

        .shop-rating span {
            color: #6b7280;
            font-size: 10px;
        }

        .shop-price {
            margin-bottom: 13px;

            color: #111827;

            font-size: 20px;

            font-weight: 900;
        }

        .shop-button {
            width: 100%;
            height: 40px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 8px;

            background: #4f46e5;

            color: white;

            text-decoration: none;

            font-size: 11px;
            font-weight: 700;
        }

        .shop-button:hover {
            background: #4338ca;
        }


        /* EMPTY */

        .empty-products {
            padding: 65px 25px;

            background: white;

            border: 1px dashed #d1d5db;

            border-radius: 15px;

            text-align: center;
        }

        .empty-products h2 {
            margin: 0 0 7px;

            color: #111827;
        }

        .empty-products p {
            margin: 0;

            color: #6b7280;

            font-size: 12px;
        }


        /* RESPONSIVE */

        @media (max-width: 1050px) {

            .shop-grid {
                grid-template-columns:
                    repeat(3, 1fr);
            }

            .filter-form {
                grid-template-columns:
                    2fr 1fr 1fr;
            }
        }

        @media (max-width: 750px) {

            .shop-grid {
                grid-template-columns:
                    repeat(2, 1fr);
            }

            .filter-form {
                grid-template-columns: 1fr;
            }

            .search-button {
                width: 100%;
            }

        }

        @media (max-width: 500px) {

            .shop-grid {
                grid-template-columns: 1fr;
            }

            .shop-image {
                height: 260px;
            }

            .products-hero h1 {
                font-size: 29px;
            }

            .products-nav a:nth-child(n+3) {
                display: none;
            }

        }

    </style>

</head>


<body>


<div class="products-page">


    <!-- NAVBAR -->

    <nav class="products-navbar">

        <div class="products-navbar-inner">


            <a
                href="/smartcart/index.php"
                class="products-logo"
            >
                🛒 Smart<span>Cart</span>
            </a>


            <div class="products-nav">

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
                >
                    Cart
                </a>


                <?php if ($is_logged_in): ?>

                    <a
                        href="/smartcart/orders.php"
                    >
                        Orders
                    </a>

                    <a
                        href="/smartcart/wishlist.php"
                    >
                        Wishlist
                    </a>

                    <?php if (
                        (
                            $_SESSION['user']['role']
                            ?? ''
                        ) === 'admin'
                    ): ?>

                        <a
                            href="/smartcart/admin/index.php"
                        >
                            Admin
                        </a>

                    <?php endif; ?>

                    <a
                        href="/smartcart/logout.php"
                        class="products-login"
                    >
                        Logout
                    </a>

                <?php else: ?>

                    <a
                        href="/smartcart/login.php"
                        class="products-login"
                    >
                        Login
                    </a>

                <?php endif; ?>


            </div>


        </div>

    </nav>


    <!-- HEADER -->

    <section class="products-hero">

        <div class="products-hero-inner">

            <h1>
                🛍️ All Products
            </h1>

            <p>
                Explore our products and find what you need.
            </p>

        </div>

    </section>


    <!-- FILTER -->

    <div class="filters">

        <form
            method="GET"
            action="/smartcart/products.php"
            class="filter-form"
        >

            <input
                type="text"
                name="search"
                placeholder="Search products, brands or categories..."
                value="<?= htmlspecialchars($search) ?>"
            >


            <select name="category">

                <option value="0">
                    All Categories
                </option>


                <?php foreach (
                    $categories as $category
                ): ?>

                    <option
                        value="<?= (int)$category['id'] ?>"
                        <?= (
                            $category_id ===
                            (int)$category['id']
                        )
                            ? 'selected'
                            : ''
                        ?>
                    >

                        <?= htmlspecialchars(
                            $category['name']
                        ) ?>

                    </option>

                <?php endforeach; ?>

            </select>


            <select name="sort">

                <option
                    value="newest"
                    <?= $sort === 'newest'
                        ? 'selected'
                        : ''
                    ?>
                >
                    Newest
                </option>

                <option
                    value="price_low"
                    <?= $sort === 'price_low'
                        ? 'selected'
                        : ''
                    ?>
                >
                    Price Low → High
                </option>

                <option
                    value="price_high"
                    <?= $sort === 'price_high'
                        ? 'selected'
                        : ''
                    ?>
                >
                    Price High → Low
                </option>

                <option
                    value="rating"
                    <?= $sort === 'rating'
                        ? 'selected'
                        : ''
                    ?>
                >
                    Highest Rated
                </option>

                <option
                    value="name"
                    <?= $sort === 'name'
                        ? 'selected'
                        : ''
                    ?>
                >
                    Name
                </option>

            </select>


            <button
                type="submit"
                class="search-button"
            >
                🔍 Search
            </button>

        </form>


        <?php if (
            $search !== '' ||
            $category_id > 0 ||
            $sort !== 'newest'
        ): ?>

            <div>

                <a
                    href="/smartcart/products.php"
                    class="clear-button"
                >
                    Clear Filters
                </a>

            </div>

        <?php endif; ?>


    </div>


    <!-- PRODUCTS -->

    <main class="products-container">


        <div class="products-top">

            Showing

            <strong>
                <?= count($products) ?>
            </strong>

            product(s)

        </div>


        <?php if (empty($products)): ?>


            <div class="empty-products">

                <h2>
                    🔍 No Products Found
                </h2>

                <p>
                    Try another search or category.
                </p>

            </div>


        <?php else: ?>


            <div class="shop-grid">


                <?php foreach (
                    $products as $product
                ): ?>


                    <article
                        class="shop-card"
                    >


                        <div class="shop-image">


                            <?php if (
                                (int)$product['stock'] > 0
                            ): ?>

                                <span
                                    class="stock-badge"
                                >
                                    ✓ In Stock
                                </span>

                            <?php else: ?>

                                <span
                                    class="stock-badge out"
                                >
                                    Out of Stock
                                </span>

                            <?php endif; ?>


                            <a
                                href="/smartcart/product.php?id=<?= (int)$product['id'] ?>"
                            >

                                <img
                                    src="<?= htmlspecialchars(
                                        smartcart_product_image(
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

                            </a>


                        </div>


                        <div class="shop-body">


                            <?php if (
                                !empty(
                                    $product['brand']
                                )
                            ): ?>

                                <span
                                    class="shop-brand"
                                >

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

                                <span
                                    class="shop-category"
                                >

                                    <?= htmlspecialchars(
                                        $product['category_name']
                                    ) ?>

                                </span>

                            <?php endif; ?>


                            <h3 class="shop-name">

                                <a
                                    href="/smartcart/product.php?id=<?= (int)$product['id'] ?>"
                                >

                                    <?= htmlspecialchars(
                                        $product['name']
                                    ) ?>

                                </a>

                            </h3>


                            <div class="shop-description">

                                <?= htmlspecialchars(
                                    $product['description']
                                    ?? ''
                                ) ?>

                            </div>


                            <div class="shop-rating">

                                ⭐

                                <?= number_format(
                                    (float)(
                                        $product['rating']
                                        ?? 0
                                    ),
                                    1
                                ) ?>

                                <span>
                                    / 5
                                </span>

                            </div>


                            <div class="shop-price">

                                ₹<?= number_format(
                                    (float)$product['price'],
                                    2
                                ) ?>

                            </div>


                            <a
                                href="/smartcart/product.php?id=<?= (int)$product['id'] ?>"
                                class="shop-button"
                            >
                                View Product
                            </a>


                        </div>


                    </article>


                <?php endforeach; ?>


            </div>


        <?php endif; ?>


    </main>


</div>


</body>

</html>