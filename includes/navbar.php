<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_logged_in =
    isset($_SESSION['user']);

$is_admin =
    $is_logged_in &&
    (
        strtolower(
            trim(
                $_SESSION['user']['role'] ?? ''
            )
        )
        === 'admin'
    );

?>

<nav class="navbar">

    <div class="navbar-container">

        <a
            href="/smartcart/index.php"
            class="logo"
        >
            🛒 Smart<span>Cart</span>
        </a>


        <div class="nav-links">

            <a href="/smartcart/index.php">
                Home
            </a>

            <a href="/smartcart/products.php">
                Products
            </a>

            <a href="/smartcart/cart.php">
                🛒 Cart
            </a>

            <?php if ($is_logged_in): ?>

                <a href="/smartcart/wishlist.php">
                    ♡ Wishlist
                </a>

                <a href="/smartcart/orders.php">
                    📦 Orders
                </a>

            <?php endif; ?>

        </div>


        <div class="nav-user">

            <?php if ($is_logged_in): ?>

                <span class="welcome">
                    Hi,
                    <?= htmlspecialchars(
                        $_SESSION['user']['name']
                    ) ?>
                </span>


                <?php if ($is_admin): ?>

                    <a
                        href="/smartcart/admin/index.php"
                        class="admin-link"
                    >
                        Admin
                    </a>

                <?php endif; ?>


                <a
                    href="/smartcart/logout.php"
                    class="logout-link"
                >
                    Logout
                </a>


            <?php else: ?>

                <a href="/smartcart/login.php">
                    Login
                </a>

                <a
                    href="/smartcart/register.php"
                    class="admin-link"
                >
                    Register
                </a>

            <?php endif; ?>

        </div>

    </div>

</nav>