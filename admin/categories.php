<?php

require_once __DIR__ . '/admin_check.php';
require_once __DIR__ . '/../config/database.php';

$error = '';


if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {

    $action =
        $_POST['action'] ?? '';


    if ($action === 'add') {

        $name =
            trim($_POST['name'] ?? '');

        $description =
            trim(
                $_POST['description'] ?? ''
            );


        if ($name === '') {

            $error =
                'Category name is required.';

        } else {

            try {

                $stmt =
                    $pdo->prepare("
                        INSERT INTO categories
                        (
                            name,
                            description
                        )
                        VALUES
                        (
                            ?,
                            ?
                        )
                    ");

                $stmt->execute([
                    $name,
                    $description
                ]);

            } catch (PDOException $e) {

                $error =
                    $e->getMessage();
            }
        }
    }


    if ($action === 'delete') {

        $id =
            (int)($_POST['id'] ?? 0);


        if ($id > 0) {

            try {

                /*
                | Set products in this category
                | to NULL before deleting category.
                */

                $stmt =
                    $pdo->prepare("
                        UPDATE products
                        SET category_id = NULL
                        WHERE category_id = ?
                    ");

                $stmt->execute([
                    $id
                ]);


                $stmt =
                    $pdo->prepare("
                        DELETE FROM categories
                        WHERE id = ?
                    ");

                $stmt->execute([
                    $id
                ]);

            } catch (PDOException $e) {

                $error =
                    $e->getMessage();
            }
        }
    }
}


$categories =
    $pdo
    ->query("
        SELECT
            id,
            name,
            description
        FROM categories
        ORDER BY id DESC
    ")
    ->fetchAll();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<title>
    Categories - SmartCart Admin
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
    max-width:1000px;
    margin:auto;
    padding:30px;
}

.grid {
    display:grid;
    grid-template-columns:350px 1fr;
    gap:20px;
}

.card {
    background:white;
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:22px;
}

h1 {
    margin-top:0;
}

h2 {
    font-size:17px;
}

.form-group {
    margin-bottom:15px;
}

label {
    display:block;
    margin-bottom:6px;
    font-size:11px;
    font-weight:bold;
}

input,
textarea {
    width:100%;
    padding:10px;
    box-sizing:border-box;
    border:1px solid #d1d5db;
    border-radius:8px;
}

button {
    padding:10px 15px;
    border:none;
    border-radius:8px;
    background:#4f46e5;
    color:white;
    cursor:pointer;
}

.category {
    padding:14px 0;
    border-bottom:1px solid #f1f5f9;
}

.category:last-child {
    border-bottom:none;
}

.category-name {
    font-weight:bold;
}

.category-description {
    color:#6b7280;
    font-size:11px;
    margin-top:4px;
}

.delete {
    margin-top:8px;
    background:#fee2e2;
    color:#b91c1c;
}

.error {
    padding:10px;
    background:#fee2e2;
    color:#991b1b;
    border-radius:8px;
    margin-bottom:15px;
    font-size:12px;
}

@media(max-width:800px) {

    .page {
        padding:15px;
    }

    .grid {
        grid-template-columns:1fr;
    }

}

</style>

</head>

<body>

<div class="page">

<h1>
    🗂 Manage Categories
</h1>

<p style="color:#6b7280;font-size:12px;">
    Add and manage product categories.
</p>

<a
    href="/smartcart/admin/index.php"
    style="color:#4f46e5;font-size:12px;font-weight:bold;"
>
    ← Dashboard
</a>

<br><br>


<?php if ($error !== ''): ?>

<div class="error">
    <?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>


<div class="grid">


    <div class="card">

        <h2>
            ➕ Add Category
        </h2>


        <form method="POST">

            <input
                type="hidden"
                name="action"
                value="add"
            >


            <div class="form-group">

                <label>
                    Category Name
                </label>

                <input
                    type="text"
                    name="name"
                    required
                >

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


            <button type="submit">
                Add Category
            </button>

        </form>

    </div>


    <div class="card">

        <h2>
            📂 Existing Categories
        </h2>


        <?php if (empty($categories)): ?>

            <p style="color:#6b7280;font-size:12px;">
                No categories found.
            </p>

        <?php else: ?>


            <?php foreach (
                $categories as $category
            ): ?>


                <div class="category">

                    <div class="category-name">

                        <?= htmlspecialchars(
                            $category['name']
                        ) ?>

                    </div>


                    <div class="category-description">

                        <?= htmlspecialchars(
                            $category['description']
                            ?? ''
                        ) ?>

                    </div>


                    <form
                        method="POST"
                        onsubmit="
                            return confirm(
                                'Delete this category?'
                            );
                        "
                    >

                        <input
                            type="hidden"
                            name="action"
                            value="delete"
                        >

                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int)$category['id'] ?>"
                        >

                        <button
                            type="submit"
                            class="delete"
                        >
                            🗑 Delete
                        </button>

                    </form>

                </div>


            <?php endforeach; ?>


        <?php endif; ?>

    </div>

</div>

</div>

</body>

</html>