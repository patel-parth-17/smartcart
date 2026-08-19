<?php

require_once __DIR__ . '/admin_check.php';
require_once __DIR__ . '/../config/database.php';


if (
    isset(
        $_POST['user_id'],
        $_POST['role']
    )
) {

    $user_id =
        (int)$_POST['user_id'];

    $role =
        strtolower(
            trim($_POST['role'])
        );


    if (
        in_array(
            $role,
            ['user', 'admin'],
            true
        )
        &&
        $user_id !==
        (int)$_SESSION['user']['id']
    ) {

        $stmt =
            $pdo->prepare("
                UPDATE users
                SET role = ?
                WHERE id = ?
            ");

        $stmt->execute([
            $role,
            $user_id
        ]);
    }


    header(
        "Location: /smartcart/admin/users.php"
    );

    exit;
}


$users =
    $pdo
    ->query("
        SELECT
            id,
            name,
            email,
            phone,
            role,
            created_at
        FROM users
        ORDER BY id DESC
    ")
    ->fetchAll();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<title>
    Users - SmartCart Admin
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

h1 {
    margin-bottom:5px;
}

.subtitle {
    color:#6b7280;
    font-size:12px;
    margin-bottom:25px;
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
    font-size:11px;
    border-top:1px solid #f1f5f9;
}

select {
    padding:7px;
    border:1px solid #d1d5db;
    border-radius:6px;
}

button {
    border:none;
    background:#4f46e5;
    color:white;
    padding:7px 10px;
    border-radius:6px;
    cursor:pointer;
}

@media(max-width:800px) {

    .page {
        padding:15px;
    }

    .card {
        overflow-x:auto;
    }

    table {
        min-width:750px;
    }
}

</style>

</head>

<body>

<div class="page">


<h1>
    👥 Manage Users
</h1>

<p class="subtitle">
    Manage SmartCart user accounts and roles.
</p>

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

<br><br>


<div class="card">

<table>

<thead>

<tr>

<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
<th>Role</th>
<th>Created</th>
<th>Action</th>

</tr>

</thead>


<tbody>

<?php foreach ($users as $user): ?>

<tr>

<td>
    <?= (int)$user['id'] ?>
</td>

<td>
    <?= htmlspecialchars(
        $user['name']
    ) ?>
</td>

<td>
    <?= htmlspecialchars(
        $user['email']
    ) ?>
</td>

<td>
    <?= htmlspecialchars(
        $user['phone'] ?? ''
    ) ?>
</td>

<td>

<form method="POST">

<input
    type="hidden"
    name="user_id"
    value="<?= (int)$user['id'] ?>"
>

<select
    name="role"
    <?= (int)$user['id'] ===
        (int)$_SESSION['user']['id']
        ? 'disabled'
        : '' ?>
>

<option
    value="user"
    <?= $user['role'] === 'user'
        ? 'selected'
        : '' ?>
>
    User
</option>

<option
    value="admin"
    <?= $user['role'] === 'admin'
        ? 'selected'
        : '' ?>
>
    Admin
</option>

</select>

</td>

<td>
    <?= htmlspecialchars(
        $user['created_at']
    ) ?>
</td>

<td>

<?php if (
    (int)$user['id']
    !==
    (int)$_SESSION['user']['id']
): ?>

<button type="submit">
    Save
</button>

<?php else: ?>

Current Admin

<?php endif; ?>

</form>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</body>

</html>