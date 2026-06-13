<?php
/**
 * MAB Shop - Site Footer Template
 * Links, newsletter, social media, and live chat widget
 */
?>
    </main>

    <!-- Footer -->
    <footer class="site-footer mt-5">
        <div class="container py-5">
            <div class="row g-4">
                <div class="col-lg-4">
                    <a href="<?= url('index.php') ?>" class="d-inline-block mb-3">
                        <img src="<?= url('assets/images/mab-shop-logo.png') ?>" alt="<?= APP_NAME ?>" class="site-logo-img site-logo-footer" height="36">
                    </a>
                    <p class="text-muted">Your trusted online shopping destination in Ghana. Quality products, secure payments, fast delivery.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                        <a href="#" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="fw-bold mb-3">Shop</h6>
                    <ul class="list-unstyled footer-links">
                        <li><a href="<?= url('products.php') ?>">All Products</a></li>
                        <li><a href="<?= url('products.php?sort=popular') ?>">Best Sellers</a></li>
                        <li><a href="<?= url('products.php?featured=1') ?>">Featured</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2">
                    <h6 class="fw-bold mb-3">Support</h6>
                    <ul class="list-unstyled footer-links">
                        <li><a href="<?= url('faq.php') ?>">FAQ</a></li>
                        <li><a href="<?= url('contact.php') ?>">Contact Us</a></li>
                        <li><a href="#">Shipping Info</a></li>
                        <li><a href="#">Returns</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="fw-bold mb-3">Newsletter</h6>
                    <p class="text-muted small">Subscribe for exclusive deals and updates.</p>
                    <form id="newsletterForm" class="d-flex gap-2">
                        <?= csrfField() ?>
                        <input type="email" name="email" class="form-control" placeholder="Your email" required>
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>
            <hr class="my-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center text-muted small">
                <span>&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</span>
                <span>Payments: <i class="bi bi-phone"></i> MoMo | <i class="bi bi-credit-card"></i> Visa/MC | PayPal Ready</span>
            </div>
        </div>
    </footer>

    <!-- Live Chat / AI Assistant Widget -->
    <div class="chat-widget" id="chatWidget">
        <button class="chat-toggle btn btn-primary rounded-circle" id="chatToggle" aria-label="Open chat" aria-expanded="false" aria-controls="chatPanel">
            <i class="bi bi-chat-dots-fill"></i>
        </button>
        <div class="chat-panel" id="chatPanel" role="dialog" aria-labelledby="chatTitle" aria-hidden="true">
            <div class="chat-header">
                <strong id="chatTitle"><i class="bi bi-robot"></i> MAB Assistant</strong>
                <button class="btn-close btn-close-white" id="chatClose" type="button" aria-label="Close chat"></button>
            </div>
            <div class="chat-messages" id="chatMessages" aria-live="polite">
                <div class="chat-msg bot">Hello! I'm your MAB shopping assistant. Ask me anything like "Show me black sneakers under GH₵300"</div>
            </div>
            <form class="chat-input" id="chatForm">
                <input type="text" id="chatInput" placeholder="Type your message..." autocomplete="off" aria-label="Chat message">
                <button type="submit" aria-label="Send message"><i class="bi bi-send-fill"></i></button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>const APP_URL = '<?= APP_URL ?>'; const CSRF_TOKEN = '<?= e($_SESSION[CSRF_TOKEN_NAME] ?? '') ?>';</script>
    <script src="<?= url('assets/js/main.js?v=' . APP_VERSION) ?>"></script>
    <?php if (!empty($extraScripts)): foreach ($extraScripts as $script): ?>
    <script src="<?= url($script) ?>"></script>
    <?php endforeach; endif; ?>
</body>
</html>
