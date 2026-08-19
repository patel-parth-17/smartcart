<?php

require_once __DIR__ . '/admin_check.php';
require_once __DIR__ . '/../config/database.php';

$error = '';
$success = '';


$categories =
    $pdo
    ->query("
        SELECT id, name
        FROM categories
        ORDER BY name ASC
    ")
    ->fetchAll();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name =
        trim($_POST['name'] ?? '');

    $description =
        trim($_POST['description'] ?? '');

    $price =
        (float)($_POST['price'] ?? 0);

    $stock =
        (int)($_POST['stock'] ?? 0);

    $brand =
        trim($_POST['brand'] ?? '');

    $rating =
        (float)($_POST['rating'] ?? 0);

    $category_id =
        (int)($_POST['category_id'] ?? 0);

    $image_name = '';


    if ($name === '') {

        $error =
            'Product name is required.';

    } elseif ($price < 0) {

        $error =
            'Price cannot be negative.';

    } elseif ($stock < 0) {

        $error =
            'Stock cannot be negative.';

    } else {


        if (
            isset($_FILES['image']) &&
            $_FILES['image']['error']
            === UPLOAD_ERR_OK
        ) {

            $allowed = [
                'jpg',
                'jpeg',
                'png',
                'webp',
                'gif'
            ];

            $filename =
                $_FILES['image']['name'];

            $extension =
                strtolower(
                    pathinfo(
                        $filename,
                        PATHINFO_EXTENSION
                    )
                );


            if (
                !in_array(
                    $extension,
                    $allowed,
                    true
                )
            ) {

                $error =
                    'Invalid image format.';

            } else {

                $image_name =
                    time() .
                    '_' .
                    preg_replace(
                        '/[^A-Za-z0-9._-]/',
                        '_',
                        $filename
                    );

                $upload_dir =
                    __DIR__ .
                    '/../uploads/';

                if (
                    !is_dir($upload_dir)
                ) {

                    mkdir(
                        $upload_dir,
                        0777,
                        true
                    );
                }


                $target =
                    $upload_dir .
                    $image_name;


                if (
                    !move_uploaded_file(
                        $_FILES['image']['tmp_name'],
                        $target
                    )
                ) {

                    $error =
                        'Unable to upload image.';

                }

            }
        }


        if ($error === '') {

            try {

                $stmt =
                    $pdo->prepare("
                        INSERT INTO products
                        (
                            category_id,
                            name,
                            description,
                            price,
                            stock,
                            image,
                            brand,
                            rating,
                            created_at
                        )

                        VALUES
                        (
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?,
                            NOW()
                        )
                    ");

                $stmt->execute([
                    $category_id > 0
                        ? $category_id
                        : null,
                    $name,
                    $description,
                    $price,
                    $stock,
                    $image_name,
                    $brand,
                    $rating
                ]);


                header(
                    "Location: /smartcart/admin/products.php"
                );

                exit;

            } catch (PDOException $e) {

                $error =
                    $e->getMessage();
            }

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<title>Add Product - SmartCart</title>

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
    max-width:850px;
    margin:auto;
    padding:30px;
}

.card {
    background:white;
    padding:25px;
    border:1px solid #e5e7eb;
    border-radius:15px;
}

h1 {
    margin-top:0;
}

.form-group {
    margin-bottom:17px;
}

label {
    display:block;
    margin-bottom:7px;
    font-size:12px;
    font-weight:700;
}

input,
textarea,
select {
    width:100%;
    padding:11px;
    border:1px solid #d1d5db;
    border-radius:8px;
    box-sizing:border-box;
}

textarea {
    resize:vertical;
}

.btn {
    display:inline-block;
    padding:11px 17px;
    border-radius:8px;
    border:none;
    background:#4f46e5;
    color:white;
    text-decoration:none;
    cursor:pointer;
    font-weight:700;
    font-size:12px;
}

.error {
    background:#fee2e2;
    color:#991b1b;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
}

</style>

</head>

<body>

<div class="page">

    <a
        href="/smartcart/admin/products.php"
        class="btn"
        style="background:#6b7280;margin-bottom:20px;"
    >
        ← Products
    </a>

    <div class="card">

        <h1>
            ➕ Add Product
        </h1>

        <?php if ($error !== ''): ?>

            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <form
            method="POST"
            enctype="multipart/form-data"
        >

            <div class="form-group">

                <label>
                    Product Name
                </label>

                <input
                    type="text"
                    name="name"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Category
                </label>

                <select
                    name="category_id"
                >

                    <option value="0">
                        Select Category
                    </option>

                    <?php foreach ($categories as $category): ?>

                        <option
                            value="<?= (int)$category['id'] ?>"
                        >

                            <?= htmlspecialchars(
                                $category['name']
                            ) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="form-group">

                <label>
                    Description
                </label>

                <textarea
                    name="description"
                    rows="5"
                ></textarea>

            </div>


            <div class="form-group">

                <label>
                    Price
                </label>

                <input
                    type="number"
                    name="price"
                    min="0"
                    step="0.01"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Stock
                </label>

                <input
                    type="number"
                    name="stock"
                    min="0"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Brand
                </label>

                <input
                    type="text"
                    name="brand"
                >

            </div>


            <div class="form-group">

                <label>
                    Rating
                </label>

                <input
                    type="number"
                    name="rating"
                    min="0"
                    max="5"
                    step="0.1"
                    value="0"
                >

            </div>


            <div class="form-group">

                <label>
                    Product Image
                </label>

                <input
                    type="file"
                    name="image"
                    accept="image/*"
                >

            </div>


            <button
                type="submit"
                class="btn"
            >
                Save Product
            </button>

        </form>

    </div>

</div>

</body>
</html>