<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';

$error = '';
$email = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email =
        trim($_POST['email'] ?? '');

    $password =
        $_POST['password'] ?? '';


    if ($email === '' || $password === '') {

        $error =
            'Please enter email and password.';

    } else {

        try {

            $stmt =
                $pdo->prepare("
                    SELECT
                        id,
                        name,
                        email,
                        phone,
                        password_hash,
                        role
                    FROM users
                    WHERE email = ?
                    LIMIT 1
                ");

            $stmt->execute([
                $email
            ]);

            $user =
                $stmt->fetch();


            if (
                !$user ||
                !password_verify(
                    $password,
                    $user['password_hash']
                )
            ) {

                $error =
                    'Invalid email or password.';

            } else {

                session_regenerate_id(true);


                $_SESSION['user'] = [

                    'id' =>
                        (int)$user['id'],

                    'name' =>
                        $user['name'],

                    'email' =>
                        $user['email'],

                    'phone' =>
                        $user['phone'],

                    'role' =>
                        strtolower(
                            trim(
                                $user['role']
                            )
                        )
                ];


                if (
                    $_SESSION['user']['role']
                    === 'admin'
                ) {

                    header(
                        'Location: /smartcart/admin/index.php'
                    );

                } else {

                    header(
                        'Location: /smartcart/index.php'
                    );

                }

                exit;
            }


        } catch (PDOException $e) {

            $error =
                'Database error: ' .
                $e->getMessage();
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
    Login - SmartCart
</title>

<link
    rel="stylesheet"
    href="/smartcart/assets/css/style.css"
>

<style>

.auth-page {
    min-height:80vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:40px 20px;
}

.auth-card {
    width:100%;
    max-width:420px;
    background:white;
    padding:30px;
    border:1px solid #e5e7eb;
    border-radius:18px;
    box-shadow:0 15px 40px rgba(0,0,0,.08);
}

.auth-logo {
    width:55px;
    height:55px;
    margin:0 auto 15px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:15px;
    background:linear-gradient(135deg,#4f46e5,#7c3aed);
    color:white;
    font-size:27px;
}

.auth-card h1 {
    text-align:center;
    margin-bottom:7px;
}

.auth-subtitle {
    text-align:center;
    color:#6b7280;
    font-size:12px;
    margin-bottom:25px;
}

.auth-error {
    background:#fee2e2;
    color:#991b1b;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
    font-size:12px;
}

.auth-form-group {
    margin-bottom:16px;
}

.auth-form-group label {
    display:block;
    margin-bottom:6px;
    font-size:12px;
    font-weight:700;
}

.auth-form-group input {
    width:100%;
    height:44px;
    padding:0 12px;
    border:1px solid #d1d5db;
    border-radius:8px;
    outline:none;
}

.auth-form-group input:focus {
    border-color:#4f46e5;
}

.auth-submit {
    width:100%;
}

.auth-bottom {
    text-align:center;
    margin-top:20px;
    font-size:12px;
    color:#6b7280;
}

.auth-bottom a {
    color:#4f46e5;
    font-weight:700;
}

</style>

</head>

<body>


<?php
include __DIR__ . '/includes/navbar.php';
?>


<div class="auth-page">

<div class="auth-card">

<div class="auth-logo">
    🛒
</div>

<h1>
    Welcome Back
</h1>

<p class="auth-subtitle">
    Login to your SmartCart account
</p>


<?php if ($error !== ''): ?>

<div class="auth-error">
    <?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>


<form method="POST">


<div class="auth-form-group">

<label>
    Email Address
</label>

<input
    type="email"
    name="email"
    value="<?= htmlspecialchars($email) ?>"
    placeholder="Enter your email"
    required
>

</div>


<div class="auth-form-group">

<label>
    Password
</label>

<input
    type="password"
    name="password"
    placeholder="Enter your password"
    required
>

</div>


<button
    type="submit"
    class="btn btn-primary auth-submit"
>
    Login
</button>


</form>


<div class="auth-bottom">

Don't have an account?

<a href="/smartcart/register.php">
    Create Account
</a>

</div>

</div>

</div>


<?php
include __DIR__ . '/includes/footer.php';
?>


</body>

</html>