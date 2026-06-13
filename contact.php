<?php
/**
 * MAB Shop - Contact & Support Page
 */
require_once __DIR__ . '/includes/bootstrap.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $pdo = getDB();
    $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        sanitizeInput($_POST['name'] ?? ''),
        sanitizeInput($_POST['email'] ?? ''),
        sanitizeInput($_POST['subject'] ?? ''),
        sanitizeInput($_POST['message'] ?? ''),
    ]);
    $message = 'Thank you! We will get back to you soon.';
}

$pageTitle = 'Contact Us';
include ROOT_PATH . '/templates/header.php';
?>

<div class="container py-5">
    <div class="row g-5">
        <div class="col-lg-5">
            <h1 class="fw-bold mb-4">Contact Us</h1>
            <p class="text-muted">Have questions? We're here to help.</p>
            <div class="mb-3"><i class="bi bi-geo-alt text-primary"></i> Accra, Ghana</div>
            <div class="mb-3"><i class="bi bi-envelope text-primary"></i> support@mabshop.com</div>
            <div class="mb-3"><i class="bi bi-phone text-primary"></i> +233 20 123 4567</div>
        </div>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm p-4">
                <?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
                <form method="POST"><?= csrfField() ?>
                    <div class="row g-3">
                        <div class="col-md-6"><input type="text" name="name" class="form-control" placeholder="Your Name" required></div>
                        <div class="col-md-6"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                        <div class="col-12"><input type="text" name="subject" class="form-control" placeholder="Subject" required></div>
                        <div class="col-12"><textarea name="message" class="form-control" rows="5" placeholder="Message" required></textarea></div>
                        <div class="col-12"><button type="submit" class="btn btn-primary">Send Message</button></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include ROOT_PATH . '/templates/footer.php'; ?>
