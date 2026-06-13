<?php
/**
 * MAB Shop - Admin Panel Header & Sidebar
 */
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Admin') ?> | <?= APP_NAME ?> Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
    <link rel="icon" type="image/png" href="<?= url('assets/images/favicon.png') ?>">
</head>
<body>
<div class="d-flex">
    <nav class="admin-sidebar">
        <div class="px-3 mb-4">
            <a href="<?= url('admin/index.php') ?>" class="d-block mb-2">
                <img src="<?= url('assets/images/mab-shop-logo.png') ?>" alt="<?= APP_NAME ?>" class="site-logo-img site-logo-admin" height="32">
            </a>
            <small class="text-muted">Admin Panel</small>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>" href="<?= url('admin/index.php') ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link <?= str_contains($_SERVER['PHP_SELF'], 'products') ? 'active' : '' ?>" href="<?= url('admin/products.php') ?>"><i class="bi bi-box-seam me-2"></i> Products</a></li>
            <li class="nav-item"><a class="nav-link <?= str_contains($_SERVER['PHP_SELF'], 'categories') ? 'active' : '' ?>" href="<?= url('admin/categories.php') ?>"><i class="bi bi-tags me-2"></i> Categories</a></li>
            <li class="nav-item"><a class="nav-link <?= str_contains($_SERVER['PHP_SELF'], 'orders') ? 'active' : '' ?>" href="<?= url('admin/orders.php') ?>"><i class="bi bi-cart-check me-2"></i> Orders</a></li>
            <li class="nav-item"><a class="nav-link <?= str_contains($_SERVER['PHP_SELF'], 'users') ? 'active' : '' ?>" href="<?= url('admin/users.php') ?>"><i class="bi bi-people me-2"></i> Users</a></li>
            <li class="nav-item"><a class="nav-link <?= str_contains($_SERVER['PHP_SELF'], 'reviews') ? 'active' : '' ?>" href="<?= url('admin/reviews.php') ?>"><i class="bi bi-star me-2"></i> Reviews</a></li>
            <li class="nav-item"><a class="nav-link <?= str_contains($_SERVER['PHP_SELF'], 'coupons') ? 'active' : '' ?>" href="<?= url('admin/coupons.php') ?>"><i class="bi bi-ticket-perforated me-2"></i> Coupons</a></li>
            <li class="nav-item mt-4"><a class="nav-link" href="<?= url('index.php') ?>"><i class="bi bi-shop me-2"></i> View Store</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= url('logout.php') ?>"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
        </ul>
    </nav>
    <div class="admin-content flex-grow-1">
        <?php $flash = getFlash(); if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show"><?= e($flash['message']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
