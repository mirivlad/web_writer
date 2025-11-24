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

    // Инициализация TinyMCE если есть текстовые редакторы
    document.addEventListener('DOMContentLoaded', function() {
        const htmlEditors = document.querySelectorAll('.html-editor');
        htmlEditors.forEach(function(editor) {
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '#' + editor.id,
                    plugins: 'advlist autolink lists link image charmap preview anchor',
                    toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image',
                    language: 'ru',
                    height: 400,
                    menubar: false,
                    statusbar: false
                });
            }
        });
    });
    </script>
</body>
</html>