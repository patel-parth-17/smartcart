<?php

header(
    'Content-Type: application/json'
);

require_once __DIR__ . '/../config/database.php';

$question =
    trim(
        $_POST['question']
        ??
        $_GET['question']
        ??
        ''
    );


if ($question === '') {

    echo json_encode([
        'success' => false,
        'message' => 'Please enter a question.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Search Products
|--------------------------------------------------------------------------
*/

$stmt =
    $pdo->prepare("
        SELECT
            p.id,
            p.name,
            p.price,
            p.stock,
            p.brand,
            p.rating,
            c.name AS category_name

        FROM products p

        LEFT JOIN categories c
            ON p.category_id = c.id

        WHERE
            p.name LIKE ?
            OR p.brand LIKE ?
            OR p.description LIKE ?
            OR c.name LIKE ?

        ORDER BY p.rating DESC

        LIMIT 5
    ");


$term =
    '%' . $question . '%';


$stmt->execute([
    $term,
    $term,
    $term,
    $term
]);


$products =
    $stmt->fetchAll();


if (empty($products)) {

    echo json_encode([
        'success' => true,
        'response' =>
            'I could not find a matching product in SmartCart. Try asking about phones, laptops, watches, shoes, or another product.',
        'products' => []
    ]);

    exit;
}


$response =
    'I found these products that may match your request:';


echo json_encode([
    'success' => true,
    'response' => $response,
    'products' => $products
]);

?>