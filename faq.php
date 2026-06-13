<?php
/**
 * MAB Shop - FAQ Page
 */
require_once __DIR__ . '/includes/bootstrap.php';

$faqs = getDB()->query('SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order')->fetchAll();
$pageTitle = 'FAQ';
include ROOT_PATH . '/templates/header.php';
?>

<div class="container py-5">
    <h1 class="fw-bold text-center mb-5">Frequently Asked Questions</h1>
    <div class="row justify-content-center"><div class="col-lg-8">
        <div class="accordion" id="faqAccordion">
            <?php foreach ($faqs as $i => $faq): ?>
            <div class="accordion-item border-0 shadow-sm mb-2">
                <h2 class="accordion-header">
                    <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= $i ?>">
                        <?= e($faq['question']) ?>
                    </button>
                </h2>
                <div id="faq<?= $i ?>" class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>" data-bs-parent="#faqAccordion">
                    <div class="accordion-body"><?= nl2br(e($faq['answer'])) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div></div>
</div>
<?php include ROOT_PATH . '/templates/footer.php'; ?>
