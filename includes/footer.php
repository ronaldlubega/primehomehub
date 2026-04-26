</main>

<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-section brand-section">
            <a href="index.php" class="logo footer-logo">
                <i class="fa-solid fa-couch"></i> 
                <span>Design</span>Haven
            </a>
            <p class="brand-tagline">Crafting beautiful spaces, one room at a time. Discover premium furniture curated for modern living.</p>
            <div class="social-links">
                <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="#" aria-label="Pinterest"><i class="fa-brands fa-pinterest"></i></a>
                <a href="#" aria-label="Twitter"><i class="fa-brands fa-twitter"></i></a>
                <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
            </div>
        </div>
        
        <div class="footer-section links-section">
            <h4>Shop</h4>
            <ul>
                <li><a href="shop.php?category=living-room">Living Room</a></li>
                <li><a href="shop.php?category=bedroom">Bedroom</a></li>
                <li><a href="shop.php?category=dining">Dining</a></li>
                <li><a href="shop.php?category=office">Home Office</a></li>
                <li><a href="shop.php?category=decor">Decor</a></li>
            </ul>
        </div>
        
        <div class="footer-section links-section">
            <h4>Design Tools</h4>
            <ul>
                <li><a href="room-planner.php">Room Planner 3D</a></li>
                <li><a href="mood-boards.php">Mood Boards</a></li>
                <li><a href="design-services.php">Design Services</a></li>
                <li><a href="inspiration.php">Inspiration Gallery</a></li>
            </ul>
        </div>
        
        <div class="footer-section contact-section">
            <h4>Support</h4>
            <ul class="contact-info">
                <li><a href="faq.php">Help & FAQ</a></li>
                <li><a href="shipping.php">Shipping & Returns</a></li>
                <li><a href="contact.php">Contact Us</a></li>
                <li><i class="fa-solid fa-envelope"></i> hello@designhaven.com</li>
                <li><i class="fa-solid fa-phone"></i> 1-800-DESIGN-9</li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Design Haven. All rights reserved.</p>
    </div>
</footer>

<!-- Toast Notifications Container -->
<div id="toast-container" class="toast-container"></div>

<!-- Core Application JS (Connects Frontend to PHP API) -->
<script src="js/theme.js"></script>
<script src="js/utils.js"></script>
<script src="js/api-client.js"></script>
<script src="js/components.js"></script>

<!-- Optional Page-Specific Scripts -->
<?php if (isset($extra_scripts)): ?>
    <?php foreach ($extra_scripts as $script): ?>
        <script src="<?= htmlspecialchars($script) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
