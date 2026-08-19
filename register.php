<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/database.php';

$error = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name =
        trim($_POST['name'] ?? '');

    $email =
        trim($_POST['email'] ?? '');

    $phone =
        trim($_POST['phone'] ?? '');

    $password =
        $_POST['password'] ?? '';

    $confirm_password =
        $_POST['confirm_password'] ?? '';


    if (
        $name === '' ||
        $email === '' ||
        $phone === '' ||
        $password === ''
    ) {

        $error =
            'Please fill all required fields.';

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            'Enter a valid email address.';

    } elseif (
        strlen($password) < 6
    ) {

        $error =
            'Password must be at least 6 characters.';

    } elseif (
        $password !== $confirm_password
    ) {

        $error =
            'Passwords do not match.';

    } else {

        try {

            $check =
                $pdo->prepare("
                    SELECT id
                    FROM users
                    WHERE email = ?
                    LIMIT 1
                ");

            $check->execute([
                $email
            ]);


            if (
                $check->fetch()
            ) {

                $error =
                    'An account with this email already exists.';

            } else {

                $hash =
                    password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );


                $stmt =
                    $pdo->prepare("
                        INSERT INTO users
                        (
                            name,
                            email,
                            phone,
                            password_hash,
                            role,
                            created_at
                        )

                        VALUES
                        (
                            ?,
                            ?,
                            ?,
                            ?,
                            'user',
                            NOW()
                        )
                    ");


                $stmt->execute([
                    $name,
                    $email,
                    $phone,
                    $hash
                ]);


                header(
                    'Location: /smartcart/login.php'
                );

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
    Register - SmartCart
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
    max-width:450px;
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

.form-row {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:12px;
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
    color:#6b7280;
    font-size:12px;
}

.auth-bottom a {
    color:#4f46e5;
    font-weight:700;
}

@media(max-width:500px) {
    .form-row {
        grid-template-columns:1fr;
    }
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
    Create Account
</h1>

<p class="auth-subtitle">
    Join SmartCart today
</p>


<?php if ($error !== ''): ?>

<div class="auth-error">
    <?= htmlspecialchars($error) ?>
</div>

<?php endif; ?>


<form method="POST">


<div class="auth-form-group">

<label>
    Full Name
</label>

<input
    type="text"
    name="name"
    placeholder="Your name"
    required
>

</div>


<div class="auth-form-group">

<label>
    Email
</label>

<input
    type="email"
    name="email"
    placeholder="you@example.com"
    required
>

</div>


<div class="auth-form-group">

<label>
    Phone
</label>

<input
    type="tel"
    name="phone"
    placeholder="Phone number"
    required
>

</div>


<div class="form-row">

<div class="auth-form-group">

<label>
    Password
</label>

<input
    type="password"
    name="password"
    required
>

</div>


<div class="auth-form-group">

<label>
    Confirm Password
</label>

<input
    type="password"
    name="confirm_password"
    required
>

</div>

</div>


<button
    type="submit"
    class="btn btn-primary auth-submit"
>
    Create Account
</button>


</form>


<div class="auth-bottom">

Already have an account?

<a href="/smartcart/login.php">
    Login
</a>

</div>

</div>

</div>


<?php
include __DIR__ . '/includes/footer.php';
?>


</body>

</html>