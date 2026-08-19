<?php

header(
    'Content-Type: application/json'
);

require_once __DIR__ . '/../config/database.php';

$query =
    trim(
        $_GET['q'] ?? ''
    );


if ($query === '') {

    echo json_encode([
        'success' => true,
        'products' => []
    ]);

    exit;
}


$stmt =
    $pdo->prepare("
        SELECT
            p.id,
            p.name,
            p.price,
            p.image,
            p.brand,
            p.rating

        FROM products p

        LEFT JOIN categories c
            ON p.category_id = c.id

        WHERE
            p.name LIKE ?
            OR p.brand LIKE ?
            OR p.description LIKE ?
            OR c.name LIKE ?

        ORDER BY p.rating DESC

        LIMIT 10
    ");


$term =
    '%' . $query . '%';


$stmt->execute([
    $term,
    $term,
    $term,
    $term
]);


$products =
    $stmt->fetchAll();


foreach (
    $products as &$product
) {

    $product['image'] =
        '/smartcart/uploads/' .
        ltrim(
            $product['image'],
            '/'
        );
}


echo json_encode([
    'success' => true,
    'products' => $products
]);

?>