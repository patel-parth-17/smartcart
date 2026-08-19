<?php

require_once __DIR__ . '/admin_check.php';
require_once __DIR__ . '/../config/database.php';


if (
    isset($_POST['status'], $_POST['order_id'])
) {

    $order_id =
        (int)$_POST['order_id'];

    $status =
        trim($_POST['status']);


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
        $order_id > 0 &&
        in_array(
            $status,
            $allowed,
            true
        )
    ) {

        $stmt =
            $pdo->prepare("
                UPDATE orders
                SET status = ?
                WHERE id = ?
            ");

        $stmt->execute([
            $status,
            $order_id
        ]);
    }


    header(
        "Location: /smartcart/admin/orders.php"
    );

    exit;
}


$orders =
    $pdo
    ->query("
        SELECT
            o.id,
            o.total_amount,
            o.shipping_address,
            o.payment_method,
            o.status,
            o.created_at,

            u.name,
            u.email

        FROM orders o

        LEFT JOIN users u
            ON o.user_id = u.id

        ORDER BY o.created_at DESC
    ")
    ->fetchAll();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<title>
    Orders - SmartCart Admin
</title>

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<style>

body {
    margin:0;
    font-family:Arial,sans-serif;
    background:#f5f7fb;
}

.page {
    max-width:1200px;
    margin:auto;
    padding:30px;
}

.header {
    margin-bottom:25px;
}

.header h1 {
    margin:0;
}

.header p {
    margin-top:5px;
    color:#6b7280;
    font-size:12px;
}

.card {
    background:white;
    border:1px solid #e5e7eb;
    border-radius:14px;
    overflow:hidden;
}

table {
    width:100%;
    border-collapse:collapse;
}

th {
    padding:13px;
    background:#f8fafc;
    text-align:left;
    font-size:10px;
}

td {
    padding:13px;
    border-top:1px solid #f1f5f9;
    font-size:11px;
    color:#4b5563;
}

select {
    padding:7px;
    border:1px solid #d1d5db;
    border-radius:6px;
    font-size:10px;
}

button {
    border:none;
    background:#4f46e5;
    color:white;
    border-radius:6px;
    padding:7px 10px;
    cursor:pointer;
    font-size:10px;
}

@media(max-width:800px) {

    .page {
        padding:15px;
    }

    .card {
        overflow-x:auto;
    }

    table {
        min-width:900px;
    }
}

</style>

</head>

<body>

<div class="page">


    <div class="header">

        <h1>
            🛍️ Manage Orders
        </h1>

        <p>
            View and update customer orders.
        </p>

        <br>

        <a
            href="/smartcart/admin/index.php"
            style="
                color:#4f46e5;
                font-size:12px;
                font-weight:bold;
            "
        >
            ← Dashboard
        </a>

    </div>


    <div class="card">

        <table>

            <thead>

                <tr>

                    <th>
                        Order
                    </th>

                    <th>
                        Customer
                    </th>

                    <th>
                        Email
                    </th>

                    <th>
                        Amount
                    </th>

                    <th>
                        Payment
                    </th>

                    <th>
                        Status
                    </th>

                    <th>
                        Date
                    </th>

                </tr>

            </thead>


            <tbody>


            <?php if (empty($orders)): ?>

                <tr>

                    <td
                        colspan="7"
                        style="text-align:center;padding:40px;"
                    >

                        No orders found.

                    </td>

                </tr>

            <?php else: ?>


                <?php foreach ($orders as $order): ?>

                    <tr>

                        <td>
                            #<?= (int)$order['id'] ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $order['name']
                                ?? 'Unknown'
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $order['email']
                                ?? ''
                            ) ?>
                        </td>

                        <td>
                            ₹<?= number_format(
                                (float)$order['total_amount'],
                                2
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $order['payment_method']
                            ) ?>
                        </td>

                        <td>

                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="order_id"
                                    value="<?= (int)$order['id'] ?>"
                                >

                                <select
                                    name="status"
                                    onchange="this.form.submit()"
                                >

                                    <?php foreach (
                                        [
                                            'pending',
                                            'paid',
                                            'processing',
                                            'shipped',
                                            'delivered',
                                            'cancelled',
                                            'failed'
                                        ]
                                        as $status
                                    ): ?>

                                        <option
                                            value="<?= $status ?>"
                                            <?= $order['status']
                                                === $status
                                                ? 'selected'
                                                : '' ?>
                                        >

                                            <?= ucfirst(
                                                $status
                                            ) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </form>

                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $order['created_at']
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