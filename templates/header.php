<?php
/**
 * MAB Shop - Site Header Template
 * Navigation, search, cart icon, dark mode toggle, and user menu
 */
$user = currentUser();
$cartCount = getCartCount();
$notifCount = $user ? getUnreadNotificationCount((int)$user['id']) : 0;
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= $user['dark_mode'] ?? ($_COOKIE['mab_theme'] ?? 'light') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($metaDescription ?? 'MAB Shop - Your premium online shopping destination in Ghana') ?>">
    <title><?= e($pageTitle) ?> | <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= url('assets/css/style.css?v=' . APP_VERSION) ?>" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= url('assets/images/favicon.png') ?>">
    <link rel="apple-touch-icon" href="<?= url('assets/images/icon-192.png') ?>">
    <link rel="manifest" href="<?= url('manifest.json') ?>">
    <meta name="theme-color" content="#1a56db">
</head>
<body>
    <!-- Top announcement bar -->
    <div class="announcement-bar text-center py-2">
        <small>Free shipping on orders over <?= formatPrice(FREE_SHIPPING_THRESHOLD) ?> | Shop with confidence at <?= APP_NAME ?></small>
    </div>

    <!-- Main navigation -->
    <nav class="navbar navbar-expand-lg navbar-main sticky-top">
        <div class="container">
            <a class="navbar-brand site-logo" href="<?= url('index.php') ?>">
                <img src="<?= url('assets/images/mab-shop-logo.png') ?>" alt="<?= APP_NAME ?>" class="site-logo-img" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <!-- Search with autocomplete -->
                <form class="d-flex mx-lg-4 flex-grow-1 my-2 my-lg-0" action="<?= url('products.php') ?>" method="GET" id="searchForm">
                    <div class="input-group search-wrapper w-100">
                        <input type="search" name="q" class="form-control" id="searchInput" placeholder="Search products or try: black sneakers under 300" autocomplete="off" value="<?= e($_GET['q'] ?? '') ?>">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                        <div class="search-suggestions" id="searchSuggestions"></div>
                    </div>
                </form>
                <ul class="navbar-nav align-items-lg-center gap-1">
                    <li class="nav-item"><a class="nav-link" href="<?= url('products.php') ?>">Shop</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('compare.php') ?>">Compare</a></li>
                    <li class="nav-item">
                        <button class="btn btn-link nav-link theme-toggle" id="themeToggle" aria-label="Toggle dark mode">
                            <i class="bi bi-moon-stars-fill"></i>
                        </button>
                    </li>
                    <?php if ($user): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link position-relative" href="<?= url('notifications.php') ?>">
                            <i class="bi bi-bell"></i>
                            <?php if ($notifCount > 0): ?><span class="badge bg-danger badge-notify"><?= $notifCount ?></span><?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><?= e($user['first_name']) ?></a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= url('dashboard.php') ?>"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?= url('orders.php') ?>"><i class="bi bi-box"></i> Orders</a></li>
                            <li><a class="dropdown-item" href="<?= url('wishlist.php') ?>"><i class="bi bi-heart"></i> Wishlist</a></li>
                            <li><a class="dropdown-item" href="<?= url('profile.php') ?>"><i class="bi bi-person"></i> Profile</a></li>
                            <?php if (isAdmin()): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= url('admin/index.php') ?>"><i class="bi bi-gear"></i> Admin Panel</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= url('logout.php') ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= url('login.php') ?>">Login</a></li>
                    <li class="nav-item"><a class="btn btn-outline-primary btn-sm ms-1" href="<?= url('register.php') ?>">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sticky floating cart icon -->
    <a href="<?= url('cart.php') ?>" class="sticky-cart" id="stickyCart" aria-label="View cart">
        <i class="bi bi-cart3"></i>
        <span class="cart-count" id="cartCountBadge"><?= $cartCount ?></span>
    </a>

    <!-- Flash messages -->
    <?php $flash = getFlash(); if ($flash): ?>
    <div class="container mt-3">
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show animate-fade-in">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>

    <main class="main-content">
