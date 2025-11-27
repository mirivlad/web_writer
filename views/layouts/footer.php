<?php
// views/layouts/footer.php
?>
        </div>
    </main>

    <footer class="bg-light mt-5 py-4 border-top">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small class="text-muted">
                        &copy; <?= date('Y') ?> <?= e(APP_NAME) ?>
                    </small>
                </div>
                <div class="col-md-6 text-md-end">
                    <?php if (is_logged_in()): ?>
                        <a href="<?= SITE_URL ?>/author/<?= $_SESSION['user_id'] ?>" target="_blank" class="text-muted text-decoration-none">
                            <i class="bi bi-eye"></i> Моя публичная страница
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </footer>
    <script src="<?= SITE_URL ?>/assets/js/quill.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Функция для установки темы
    function setTheme(themeName) {
        // Устанавливаем cookie на 30 дней
        const date = new Date();
        date.setTime(date.getTime() + (30 * 24 * 60 * 60 * 1000));
        const expires = "expires=" + date.toUTCString();
        document.cookie = "bs_theme=" + themeName + ";" + expires + ";path=/";
        
        // Перезагружаем страницу для применения темы
        window.location.reload();
    }

    // Функция для получения цвета темы (для превью)
    function getThemeColor(theme) {
        const themeColors = {
            'default': '#0d6efd',
            'cerulean': '#2fa4e7',
            'cosmo': '#2780e3',
            'cyborg': '#060606',
            'darkly': '#375a7f',
            'flatly': '#2c3e50',
            'journal': '#eb6864',
            'litera': '#4582ec',
            'lumen': '#158cba',
            'lux': '#1a1a1a',
            'materia': '#2196f3',
            'minty': '#78c2ad',
            'morph': '#6750a4',
            'pulse': '#593196',
            'quartz': '#d7ccc8',
            'sandstone': '#325d88',
            'simplex': '#d9230f',
            'sketchy': '#333333',
            'slate': '#484e5a',
            'solar': '#b58900',
            'spacelab': '#446e9b',
            'superhero': '#4e5d6c',
            'united': '#e95420',
            'vapor': '#0d6efd',
            'yeti': '#008cba',
            'zephyr': '#0d6efd'
        };
        return themeColors[theme] || '#0d6efd';
    }

    // Применяем цвет превью для всех элементов
    document.addEventListener('DOMContentLoaded', function() {
        const themePreviews = document.querySelectorAll('.theme-preview');
        themePreviews.forEach(preview => {
            const themeOption = preview.closest('.theme-option');
            if (themeOption) {
                const theme = themeOption.getAttribute('onclick').match(/setTheme\('([^']+)'\)/)[1];
                preview.style.backgroundColor = getThemeColor(theme);
            }
        });
    });

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