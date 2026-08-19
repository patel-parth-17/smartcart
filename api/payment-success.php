<?php

require_once __DIR__ . '/../auth/auth_check.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/razorpay.php';

$user_id =
    (int)$_SESSION['user']['id'];

$payment_id =
    $_POST['razorpay_payment_id']
    ?? '';

$razorpay_order_id =
    $_POST['razorpay_order_id']
    ?? '';

$signature =
    $_POST['razorpay_signature']
    ?? '';

$address =
    trim(
        $_POST['shipping_address']
        ?? ''
    );


if (
    $payment_id === '' ||
    $razorpay_order_id === '' ||
    $signature === '' ||
    $address === ''
) {

    die(
        'Invalid payment response.'
    );

}


/*
|--------------------------------------------------------------------------
| VERIFY SIGNATURE
|--------------------------------------------------------------------------
*/

$expected_signature =
    hash_hmac(
        'sha256',
        $razorpay_order_id .
        '|' .
        $payment_id,
        RAZORPAY_KEY_SECRET
    );


if (
    !hash_equals(
        $expected_signature,
        $signature
    )
) {

    die(
        'Payment verification failed.'
    );

}


/*
|--------------------------------------------------------------------------
| GET CURRENT CART
|--------------------------------------------------------------------------
*/

$stmt =
    $pdo->prepare("
        SELECT
            c.quantity,
            p.id AS product_id,
            p.name,
            p.price,
            p.stock

        FROM cart c

        INNER JOIN products p
            ON c.product_id = p.id

        WHERE c.user_id = ?
    ");

$stmt->execute([
    $user_id
]);

$cart =
    $stmt->fetchAll();


if (empty($cart)) {

    die(
        'Cart is empty.'
    );

}


$total =
    0;


foreach (
    $cart as $item
) {

    if (
        (int)$item['stock']
        <
        (int)$item['quantity']
    ) {

        die(
            'Insufficient stock for ' .
            htmlspecialchars(
                $item['name']
            )
        );
    }


    $total +=
        (float)$item['price']
        *
        (int)$item['quantity'];
}


/*
|--------------------------------------------------------------------------
| CREATE ORDER
|--------------------------------------------------------------------------
*/

$pdo->beginTransaction();

try {

    $stmt =
        $pdo->prepare("
            INSERT INTO orders
            (
                user_id,
                total_amount,
                shipping_address,
                payment_method,
                razorpay_order_id,
                razorpay_payment_id,
                razorpay_signature,
                status,
                created_at
            )

            VALUES
            (
                ?,
                ?,
                ?,
                'Razorpay',
                ?,
                ?,
                ?,
                'paid',
                NOW()
            )
        ");

    $stmt->execute([
        $user_id,
        $total,
        $address,
        $razorpay_order_id,
        $payment_id,
        $signature
    ]);


    $order_id =
        (int)$pdo->lastInsertId();


    foreach (
        $cart as $item
    ) {

        $stmt =
            $pdo->prepare("
                INSERT INTO order_items
                (
                    order_id,
                    product_id,
                    quantity,
                    price
                )

                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?
                )
            ");

        $stmt->execute([
            $order_id,
            $item['product_id'],
            $item['quantity'],
            $item['price']
        ]);


        $stmt =
            $pdo->prepare("
                UPDATE products

                SET stock = stock - ?

                WHERE id = ?

                AND stock >= ?
            ");

        $stmt->execute([
            $item['quantity'],
            $item['product_id'],
            $item['quantity']
        ]);
    }


    $stmt =
        $pdo->prepare("
            DELETE FROM cart
            WHERE user_id = ?
        ");

    $stmt->execute([
        $user_id
    ]);


    $pdo->commit();


    header(
        'Location: /smartcart/order-details.php?id=' .
        $order_id
    );

    exit;


} catch (Exception $e) {

    $pdo->rollBack();

    die(
        'Order processing failed: ' .
        htmlspecialchars(
            $e->getMessage()
        )
    );

}