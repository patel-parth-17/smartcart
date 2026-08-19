<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if (is_admin()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email =
        trim($_POST['email'] ?? '');

    $password =
        $_POST['password'] ?? '';

    $stmt = $pdo->prepare("
        SELECT *
        FROM users
        WHERE email = ?
        AND role = 'admin'
    ");

    $stmt->execute([
        $email
    ]);

    $user = $stmt->fetch();

    if (
        $user &&
        password_verify(
            $password,
            $user['password_hash']
        )
    ) {

        $_SESSION['user'] =
            $user;

        redirect('index.php');

    } else {

        $error =
            'Invalid admin credentials.';
    }
}

include __DIR__ . '/../includes/header.php';

?>

<main class="container narrow">

    <h1>Admin Login</h1>

    <?php if ($error): ?>

        <div class="alert error">
            <?= e($error) ?>
        </div>

    <?php endif; ?>

    <form method="POST">

        <input
            type="email"
            name="email"
            placeholder="Admin Email"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="Password"
            required
        >

        <button class="btn">
            Admin Login
        </button>

    </form>

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>