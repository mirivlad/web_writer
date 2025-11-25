<?php
// views/layouts/footer.php
?>
    </main>

    <footer class="container" style="margin-top: 3rem; padding-top: 1rem; border-top: 1px solid var(--muted-border-color);">
        <small>
            &copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. 
            <?php if (is_logged_in()): ?>
                <a href="<?= SITE_URL ?>/author/<?= $_SESSION['user_id'] ?>" target="_blank">Моя публичная страница</a>
            <?php endif; ?>
        </small>
    </footer>
    
    <script>
    // Глобальные функции JavaScript
    function confirmAction(message) {
        return confirm(message || 'Вы уверены?');
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            alert('Скопировано в буфер обмена');
        }, function(err) {
            console.error('Ошибка копирования: ', err);
        });
    }
    </script>
</body>
</html>