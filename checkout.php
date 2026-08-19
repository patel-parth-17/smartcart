<?php

require_once __DIR__ . '/auth/auth_check.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/razorpay.php';
require_once __DIR__ . '/includes/functions.php';

$user_id =
    (int)$_SESSION['user']['id'];

$error = '';

$razorpay_order_id = '';


/*
|--------------------------------------------------------------------------
| GET CART
|--------------------------------------------------------------------------
*/

$stmt =
    $pdo->prepare("
        SELECT
            c.quantity,
            p.id AS product_id,
            p.name,
            p.price,
            p.stock,
            p.image

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

    header(
        'Location: /smartcart/cart.php'
    );

    exit;
}


$total =
    0;


foreach (
    $cart as $item
) {

    $total +=
        (float)$item['price']
        *
        (int)$item['quantity'];
}


/*
|--------------------------------------------------------------------------
| CHECKOUT
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {

    $address =
        trim(
            $_POST['shipping_address']
            ?? ''
        );

    $payment_method =
        $_POST['payment_method']
        ?? 'cod';


    if ($address === '') {

        $error =
            'Shipping address is required.';

    } elseif (
        $payment_method === 'cod'
    ) {


        /*
        | COD ORDER
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
                        status,
                        created_at
                    )

                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        ?,
                        'pending',
                        NOW()
                    )
                ");

            $stmt->execute([
                $user_id,
                $total,
                $address,
                'Cash on Delivery'
            ]);


            $order_id =
                (int)$pdo->lastInsertId();


            foreach (
                $cart as $item
            ) {

                if (
                    (int)$item['stock']
                    <
                    (int)$item['quantity']
                ) {

                    throw new Exception(
                        'Insufficient stock for ' .
                        $item['name']
                    );
                }


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

                        SET stock =
                            stock - ?

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

            $error =
                $e->getMessage();
        }

    } elseif (
        $payment_method === 'razorpay'
    ) {


        /*
        |--------------------------------------------------------------------------
        | RAZORPAY ORDER
        |--------------------------------------------------------------------------
        */

        $amount_paise =
            (int)round(
                $total * 100
            );


        $payload = [
            'amount' =>
                $amount_paise,

            'currency' =>
                'INR',

            'receipt' =>
                'SC_' .
                $user_id .
                '_' .
                time()
        ];


        $ch =
            curl_init(
                'https://api.razorpay.com/v1/orders'
            );


        curl_setopt_array(
            $ch,
            [

                CURLOPT_RETURNTRANSFER =>
                    true,

                CURLOPT_POST =>
                    true,

                CURLOPT_USERPWD =>
                    RAZORPAY_KEY_ID .
                    ':' .
                    RAZORPAY_KEY_SECRET,

                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json'
                ],

                CURLOPT_POSTFIELDS =>
                    json_encode($payload),

                CURLOPT_TIMEOUT =>
                    30
            ]
        );


        $response =
            curl_exec($ch);

        $http_code =
            curl_getinfo(
                $ch,
                CURLINFO_HTTP_CODE
            );

        $curl_error =
            curl_error($ch);

        curl_close($ch);


        if ($curl_error !== '') {

            $error =
                'Payment connection error: ' .
                $curl_error;

        } else {

            $data =
                json_decode(
                    $response,
                    true
                );


            if (
                $http_code >= 200 &&
                $http_code < 300 &&
                !empty($data['id'])
            ) {

                $razorpay_order_id =
                    $data['id'];

            } else {

                $error =
                    $data['error']['description']
                    ??
                    'Unable to create Razorpay order.';
            }
        }

    }

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
    Checkout - SmartCart
</title>

<link
    rel="stylesheet"
    href="/smartcart/assets/css/style.css"
>

<style>

.checkout-page {
    width:92%;
    max-width:1050px;
    margin:40px auto;
}

.checkout-grid {
    display:grid;
    grid-template-columns:1fr 350px;
    gap:20px;
}

.checkout-card {
    background:white;
    border:1px solid #e5e7eb;
    border-radius:15px;
    padding:22px;
}

.checkout-card textarea {
    width:100%;
    min-height:130px;
    padding:12px;
    border:1px solid #d1d5db;
    border-radius:9px;
    resize:vertical;
}

.payment-option {
    display:flex;
    align-items:center;
    gap:12px;
    padding:15px;
    margin-top:10px;
    border:1px solid #e5e7eb;
    border-radius:10px;
    cursor:pointer;
}

.payment-option:hover {
    background:#eef2ff;
    border-color:#a5b4fc;
}

.checkout-item {
    display:flex;
    justify-content:space-between;
    gap:10px;
    padding:11px 0;
    border-bottom:1px solid #f1f5f9;
    font-size:12px;
}

.checkout-total {
    display:flex;
    justify-content:space-between;
    margin-top:18px;
    font-size:20px;
    font-weight:900;
}

.checkout-btn {
    width:100%;
    margin-top:18px;
}

.checkout-error {
    background:#fee2e2;
    color:#991b1b;
    padding:12px;
    border-radius:8px;
    margin-bottom:18px;
    font-size:12px;
}

@media(max-width:800px) {
    .checkout-grid {
        grid-template-columns:1fr;
    }
}

</style>

</head>

<body>


<?php
include __DIR__ . '/includes/navbar.php';
?>


<main class="checkout-page">


<h1>
    Checkout
</h1>

<p style="color:#6b7280;font-size:12px;margin-bottom:20px;">
    Complete your order.
</p>


<?php if ($error !== ''): ?>

<div class="checkout-error">
    <?= e($error) ?>
</div>

<?php endif; ?>


<div class="checkout-grid">


<div class="checkout-card">


<h2>
    🚚 Shipping Address
</h2>


<form
    method="POST"
    id="checkoutForm"
>


<textarea
    name="shipping_address"
    placeholder="Enter complete delivery address"
    required
></textarea>


<h2 style="margin-top:25px;">
    💳 Payment Method
</h2>


<label class="payment-option">

<input
    type="radio"
    name="payment_method"
    value="cod"
    checked
>

<div>

<strong>
    💵 Cash on Delivery
</strong>

<br>

<small>
    Pay when your order arrives.
</small>

</div>

</label>


<label class="payment-option">

<input
    type="radio"
    name="payment_method"
    value="razorpay"
>

<div>

<strong>
    💳 Pay Online
</strong>

<br>

<small>
    UPI, cards and other Razorpay payment methods.
</small>

</div>

</label>


<button
    type="submit"
    class="btn btn-primary checkout-btn"
>
    Place Order
</button>


</form>


</div>


<div class="checkout-card">

<h2>
    🛒 Order Summary
</h2>


<?php foreach (
    $cart as $item
): ?>

<div class="checkout-item">

<span>

<?= e(
    $item['name']
) ?>

× <?= (int)$item['quantity'] ?>

</span>


<strong>

<?= currency(
    $item['price']
    *
    $item['quantity']
) ?>

</strong>

</div>

<?php endforeach; ?>


<div class="checkout-total">

<span>
    Total
</span>

<span>
    <?= currency($total) ?>
</span>

</div>

</div>


</div>

</main>


<?php if (
    $razorpay_order_id !== ''
): ?>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>

const options = {

    key:
        <?= json_encode(
            RAZORPAY_KEY_ID
        ) ?>,

    amount:
        <?= (int)round(
            $total * 100
        ) ?>,

    currency:
        "INR",

    name:
        "SmartCart",

    description:
        "SmartCart Order",

    order_id:
        <?= json_encode(
            $razorpay_order_id
        ) ?>,

    handler:
        function(response) {

            const form =
                document.createElement(
                    'form'
                );

            form.method =
                'POST';

            form.action =
                '/smartcart/api/payment-success.php';


            const address =
                document.querySelector(
                    '[name="shipping_address"]'
                ).value;


            addInput(
                form,
                'razorpay_payment_id',
                response.razorpay_payment_id
            );

            addInput(
                form,
                'razorpay_order_id',
                response.razorpay_order_id
            );

            addInput(
                form,
                'razorpay_signature',
                response.razorpay_signature
            );

            addInput(
                form,
                'shipping_address',
                address
            );


            document.body.appendChild(form);

            form.submit();
        }
};


function addInput(
    form,
    name,
    value
) {

    const input =
        document.createElement(
            'input'
        );

    input.type =
        'hidden';

    input.name =
        name;

    input.value =
        value;

    form.appendChild(input);
}


const razorpay =
    new Razorpay(options);


razorpay.on(
    'payment.failed',
    function() {

        alert(
            'Payment failed. Please try again.'
        );

    }
);


razorpay.open();

</script>

<?php endif; ?>


<?php
include __DIR__ . '/includes/footer.php';
?>


</body>

</html>