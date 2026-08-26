<?php
/**
 * CargoFlow — Admin layout footer
 */
?>
        </main>
        <footer class="px-4 pb-4 text-muted-2 small">
            <div class="d-flex flex-wrap justify-content-between gap-2">
                <span>&copy; <?= date('Y') ?> CargoFlow Admin · v<?= defined('APP_VERSION') ? e(APP_VERSION) : '1.0' ?></span>
                <span>Powered by PHP + MySQL</span>
            </div>
        </footer>
    </div>
</div>

<div class="cf-toast" aria-live="polite" aria-atomic="true"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('admin/assets/admin.js') ?>"></script>
</body>
</html>
