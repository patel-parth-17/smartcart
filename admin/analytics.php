<?php

require_once __DIR__ . '/admin_check.php';
require_once __DIR__ . '/../config/database.php';


$total_sales =
    (float)$pdo
    ->query("
        SELECT COALESCE(
            SUM(total_amount), 0
        )
        FROM orders
        WHERE status NOT IN
        ('cancelled', 'failed')
    ")
    ->fetchColumn();


$total_orders =
    (int)$pdo
    ->query("
        SELECT COUNT(*)
        FROM orders
    ")
    ->fetchColumn();


$paid_orders =
    (int)$pdo
    ->query("
        SELECT COUNT(*)
        FROM orders
        WHERE status IN
        ('paid','processing','shipped','delivered')
    ")
    ->fetchColumn();


$pending_orders =
    (int)$pdo
    ->query("
        SELECT COUNT(*)
        FROM orders
        WHERE status = 'pending'
    ")
    ->fetchColumn();


$average_order =
    (float)$pdo
    ->query("
        SELECT COALESCE(
            AVG(total_amount), 0
        )
        FROM orders
        WHERE status NOT IN
        ('cancelled','failed')
    ")
    ->fetchColumn();


$top_products =
    $pdo
    ->query("
        SELECT
            p.name,
            SUM(oi.quantity) AS sold_quantity,
            SUM(
                oi.quantity * oi.price
            ) AS revenue

        FROM order_items oi

        INNER JOIN products p
            ON oi.product_id = p.id

        INNER JOIN orders o
            ON oi.order_id = o.id

        WHERE o.status NOT IN
        ('cancelled','failed')

        GROUP BY
            oi.product_id,
            p.name

        ORDER BY sold_quantity DESC

        LIMIT 10
    ")
    ->fetchAll();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<title>
    Analytics - SmartCart Admin
</title>

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<style>

body {
    margin:0;
    background:#f5f7fb;
    font-family:Arial,sans-serif;
}

.page {
    max-width:1100px;
    margin:auto;
    padding:30px;
}

.stats {
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:15px;
    margin-top:25px;
}

.stat {
    background:white;
    padding:20px;
    border:1px solid #e5e7eb;
    border-radius:13px;
}

.stat-label {
    color:#6b7280;
    font-size:10px;
}

.stat-value {
    margin-top:6px;
    color:#111827;
    font-size:24px;
    font-weight:900;
}

.card {
    margin-top:25px;
    background:white;
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:22px;
}

table {
    width:100%;
    border-collapse:collapse;
}

th {
    background:#f8fafc;
    padding:12px;
    font-size:10px;
    text-align:left;
}

td {
    padding:12px;
    border-top:1px solid #f1f5f9;
    font-size:11px;
}

@media(max-width:800px) {

    .stats {
        grid-template-columns:repeat(2,1fr);
    }

}

@media(max-width:500px) {

    .page {
        padding:15px;
    }

    .stats {
        grid-template-columns:1fr;
    }

}

</style>

</head>

<body>

<div class="page">

<h1>
    📊 Store Analytics
</h1>

<p style="color:#6b7280;font-size:12px;">
    Overview of your SmartCart store performance.
</p>

<a
    href="/smartcart/admin/index.php"
    style="color:#4f46e5;font-size:12px;font-weight:bold;"
>
    ← Dashboard
</a>


<div class="stats">


    <div class="stat">
        <div class="stat-label">
            TOTAL SALES
        </div>

        <div class="stat-value">
            ₹<?= number_format(
                $total_sales,
                2
            ) ?>
        </div>
    </div>


    <div class="stat">
        <div class="stat-label">
            TOTAL ORDERS
        </div>

        <div class="stat-value">
            <?= $total_orders ?>
        </div>
    </div>


    <div class="stat">
        <div class="stat-label">
            COMPLETED / PAID
        </div>

        <div class="stat-value">
            <?= $paid_orders ?>
        </div>
    </div>


    <div class="stat">
        <div class="stat-label">
            AVERAGE ORDER
        </div>

        <div class="stat-value">
            ₹<?= number_format(
                $average_order,
                2
            ) ?>
        </div>
    </div>


</div>


<div class="card">

<h2>
    🔥 Top Products
</h2>

<table>

<thead>

<tr>

<th>
    Product
</th>

<th>
    Units Sold
</th>

<th>
    Revenue
</th>

</tr>

</thead>


<tbody>


<?php if (
    empty($top_products)
): ?>

<tr>

<td colspan="3">

    No sales data available.

</td>

</tr>

<?php else: ?>


<?php foreach (
    $top_products
    as $product
): ?>

<tr>

<td>
    <?= htmlspecialchars(
        $product['name']
    ) ?>
</td>

<td>
    <?= (int)$product['sold_quantity'] ?>
</td>

<td>
    ₹<?= number_format(
        (float)$product['revenue'],
        2
    ) ?>
</td>

</tr>

<?php endforeach; ?>


<?php endif; ?>


</tbody>

</table>

</div>

</div>

</body>

</html>