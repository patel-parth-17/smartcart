<?php

header(
    'Content-Type: application/json'
);

require_once __DIR__ . '/../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user'])) {

    echo json_encode([
        'success' => false,
        'message' => 'Please login first.'
    ]);

    exit;
}


$user_id =
    (int)$_SESSION['user']['id'];

$action =
    $_POST['action'] ?? '';

$product_id =
    (int)($_POST['product_id'] ?? 0);

$quantity =
    (int)($_POST['quantity'] ?? 1);


if ($product_id <= 0) {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid product.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| PRODUCT
|--------------------------------------------------------------------------
*/

$stmt =
    $pdo->prepare("
        SELECT id, stock
        FROM products
        WHERE id = ?
        LIMIT 1
    ");

$stmt->execute([
    $product_id
]);

$product =
    $stmt->fetch();


if (!$product) {

    echo json_encode([
        'success' => false,
        'message' => 'Product not found.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| ADD
|--------------------------------------------------------------------------
*/

if ($action === 'add') {

    $quantity =
        max(1, $quantity);

    if (
        $quantity >
        (int)$product['stock']
    ) {

        echo json_encode([
            'success' => false,
            'message' => 'Not enough stock.'
        ]);

        exit;
    }


    $stmt =
        $pdo->prepare("
            SELECT quantity
            FROM cart
            WHERE user_id = ?
            AND product_id = ?
            LIMIT 1
        ");

    $stmt->execute([
        $user_id,
        $product_id
    ]);

    $existing =
        $stmt->fetch();


    if ($existing) {

        $new_quantity =
            (int)$existing['quantity']
            +
            $quantity;


        if (
            $new_quantity >
            (int)$product['stock']
        ) {

            echo json_encode([
                'success' => false,
                'message' => 'Not enough stock.'
            ]);

            exit;
        }


        $stmt =
            $pdo->prepare("
                UPDATE cart
                SET quantity = ?
                WHERE user_id = ?
                AND product_id = ?
            ");

        $stmt->execute([
            $new_quantity,
            $user_id,
            $product_id
        ]);

    } else {

        $stmt =
            $pdo->prepare("
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

        $stmt->execute([
            $user_id,
            $product_id,
            $quantity
        ]);
    }


    echo json_encode([
        'success' => true,
        'message' => 'Product added to cart.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| UPDATE
|--------------------------------------------------------------------------
*/

if ($action === 'update') {

    if ($quantity <= 0) {

        $stmt =
            $pdo->prepare("
                DELETE FROM cart
                WHERE user_id = ?
                AND product_id = ?
            ");

        $stmt->execute([
            $user_id,
            $product_id
        ]);

    } else {

        if (
            $quantity >
            (int)$product['stock']
        ) {

            echo json_encode([
                'success' => false,
                'message' => 'Not enough stock.'
            ]);

            exit;
        }


        $stmt =
            $pdo->prepare("
                UPDATE cart
                SET quantity = ?
                WHERE user_id = ?
                AND product_id = ?
            ");

        $stmt->execute([
            $quantity,
            $user_id,
            $product_id
        ]);
    }


    echo json_encode([
        'success' => true,
        'message' => 'Cart updated.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| REMOVE
|--------------------------------------------------------------------------
*/

if ($action === 'remove') {

    $stmt =
        $pdo->prepare("
            DELETE FROM cart
            WHERE user_id = ?
            AND product_id = ?
        ");

    $stmt->execute([
        $user_id,
        $product_id
    ]);


    echo json_encode([
        'success' => true,
        'message' => 'Product removed.'
    ]);

    exit;
}


echo json_encode([
    'success' => false,
    'message' => 'Invalid action.'
]);

?>