<article class="card">

    <a href="<?= $base ?? '' ?>product.php?id=<?= $p['id'] ?>">

        <img
            src="<?= e(product_image($p['image'])) ?>"
            alt="<?= e($p['name']) ?>"
        >

    </a>

    <p class="muted">
        <?= e($p['brand']) ?>
    </p>

    <h3>
        <?= e($p['name']) ?>
    </h3>

    <p>
        ₹<?= number_format($p['price'], 2) ?>
    </p>

    <p>
        ⭐ <?= e($p['rating']) ?>
    </p>

    <a
        class="btn"
        href="<?= $base ?? '' ?>product.php?id=<?= $p['id'] ?>"
    >
        View Product
    </a>

</article>