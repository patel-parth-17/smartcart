<?php

require_once __DIR__ . '/admin_check.php';
require_once __DIR__ . '/../config/database.php';


try {

    $stmt = $pdo->query("
        SELECT
            p.id,
            p.name,
            p.price,
            p.stock,
            p.image,
            p.brand,
            p.rating,
            c.name AS category_name

        FROM products p

        LEFT JOIN categories c
            ON p.category_id = c.id

        ORDER BY p.id DESC
    ");

    $products = $stmt->fetchAll();

} catch (PDOException $e) {

    die(
        "Products error: " .
        htmlspecialchars($e->getMessage())
    );
}


function admin_product_image($image)
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
    Manage Products - SmartCart
</title>

<style>

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f5f7fb;
    color: #111827;
}

.page {
    max-width: 1200px;
    margin: auto;
    padding: 30px;
}

.header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 25px;
}

.header h1 {
    margin: 0;
    font-size: 27px;
}

.header p {
    margin: 5px 0 0;
    color: #6b7280;
    font-size: 12px;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 11px 16px;

    border-radius: 8px;

    background: #4f46e5;
    color: white;

    text-decoration: none;

    font-size: 12px;
    font-weight: 700;

    border: none;
    cursor: pointer;
}

.btn:hover {
    background: #4338ca;
}

.card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    overflow: hidden;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    padding: 14px;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;

    color: #374151;
    font-size: 11px;
    text-align: left;
}

td {
    padding: 14px;
    border-bottom: 1px solid #f1f5f9;

    font-size: 12px;
    color: #4b5563;
}

tr:hover td {
    background: #fafafa;
}

.product-img {
    width: 60px;
    height: 60px;

    object-fit: contain;

    border-radius: 8px;

    background: #f3f4f6;
}

.status {
    display: inline-block;

    padding: 5px 9px;

    border-radius: 50px;

    background: #dcfce7;

    color: #15803d;

    font-size: 9px;
    font-weight: 700;
}

.status.out {
    background: #fee2e2;
    color: #b91c1c;
}

.actions {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.edit {
    background: #eef2ff;
    color: #4338ca;
}

.delete {
    background: #fee2e2;
    color: #b91c1c;
}

.empty {
    padding: 60px;
    text-align: center;
}

.empty h2 {
    margin-bottom: 6px;
}

.empty p {
    color: #6b7280;
    font-size: 12px;
}

@media(max-width:800px) {

    .page {
        padding: 15px;
    }

    .header {
        flex-direction: column;
        align-items: flex-start;
    }

    .card {
        overflow-x: auto;
    }

    table {
        min-width: 850px;
    }

}

</style>

</head>

<body>

<div class="page">


    <div class="header">

        <div>

            <h1>
                📦 Manage Products
            </h1>

            <p>
                View, edit and remove products.
            </p>

        </div>

        <div>

            <a
                href="/smartcart/admin/index.php"
                class="btn"
                style="
                    background:#6b7280;
                    margin-right:5px;
                "
            >
                ← Dashboard
            </a>

            <a
                href="/smartcart/admin/add-product.php"
                class="btn"
            >
                ➕ Add Product
            </a>

        </div>

    </div>


    <div class="card">

        <?php if (empty($products)): ?>

            <div class="empty">

                <h2>
                    No Products Found
                </h2>

                <p>
                    Add your first product.
                </p>

                <br>

                <a
                    href="/smartcart/admin/add-product.php"
                    class="btn"
                >
                    ➕ Add Product
                </a>

            </div>

        <?php else: ?>

            <table>

                <thead>

                    <tr>

                        <th>Image</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Rating</th>
                        <th>Actions</th>

                    </tr>

                </thead>


                <tbody>

                <?php foreach ($products as $product): ?>

                    <tr>

                        <td>

                            <img
                                class="product-img"
                                src="<?= htmlspecialchars(
                                    admin_product_image(
                                        $product['image']
                                    )
                                ) ?>"
                                alt="Product"
                                onerror="
                                    this.onerror=null;
                                    this.src='/smartcart/assets/images/no-image.png';
                                "
                            >

                        </td>

                        <td>

                            <strong>
                                <?= htmlspecialchars(
                                    $product['name']
                                ) ?>
                            </strong>

                            <?php if (!empty($product['brand'])): ?>

                                <br>

                                <small>
                                    <?= htmlspecialchars(
                                        $product['brand']
                                    ) ?>
                                </small>

                            <?php endif; ?>

                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $product['category_name']
                                ?? 'Uncategorized'
                            ) ?>
                        </td>

                        <td>
                            ₹<?= number_format(
                                (float)$product['price'],
                                2
                            ) ?>
                        </td>

                        <td>

                            <?php if (
                                (int)$product['stock'] > 0
                            ): ?>

                                <span class="status">
                                    <?= (int)$product['stock'] ?>
                                </span>

                            <?php else: ?>

                                <span class="status out">
                                    Out
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>
                            ⭐ <?= number_format(
                                (float)$product['rating'],
                                1
                            ) ?>
                        </td>

                        <td>

                            <div class="actions">

                                <a
                                    href="/smartcart/admin/edit-product.php?id=<?= (int)$product['id'] ?>"
                                    class="btn edit"
                                >
                                    ✏ Edit
                                </a>

                                <a
                                    href="/smartcart/admin/products.php?delete=<?= (int)$product['id'] ?>"
                                    class="btn delete"
                                    onclick="
                                        return confirm(
                                            'Delete this product?'
                                        );
                                    "
                                >
                                    🗑 Delete
                                </a>

                            </div>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        <?php endif; ?>

    </div>

</div>


<?php

if (isset($_GET['delete'])) {

    $delete_id =
        (int)$_GET['delete'];

    if ($delete_id > 0) {

        try {

            $stmt =
                $pdo->prepare("
                    DELETE FROM products
                    WHERE id = ?
                ");

            $stmt->execute([
                $delete_id
            ]);

            header(
                "Location: /smartcart/admin/products.php"
            );

            exit;

        } catch (PDOException $e) {

            die(
                "Delete error: " .
                htmlspecialchars(
                    $e->getMessage()
                )
            );
        }
    }
}

?>

</body>
</html>