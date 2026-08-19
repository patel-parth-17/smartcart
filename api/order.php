<?php

header(
    'Content-Type: application/json'
);

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['user'])) {

    echo json_encode([
        'success' => false,
        'message' => 'Please login.'
    ]);

    exit;
}


$user_id =
    (int)$_SESSION['user']['id'];


$order_id =
    (int)($_GET['id'] ?? 0);


if ($order_id <= 0) {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid order.'
    ]);

    exit;
}


$stmt =
    $pdo->prepare("
        SELECT
            id,
            total_amount,
            shipping_address,
            payment_method,
            status,
            created_at

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

    echo json_encode([
        'success' => false,
        'message' => 'Order not found.'
    ]);

    exit;
}


$stmt =
    $pdo->prepare("
        SELECT
            oi.product_id,
            oi.quantity,
            oi.price,
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


$order['items'] =
    $stmt->fetchAll();


echo json_encode([
    'success' => true,
    'order' => $order
]);

?>