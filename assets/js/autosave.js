// assets/js/autosave.js
document.addEventListener('DOMContentLoaded', function() {
    // Ждем инициализации редактора
    setTimeout(() => {
        initializeAutoSave();
    }, 1000);
});

function initializeAutoSave() {
    console.log('AutoSave: Initializing...');
    
    // Ищем активные редакторы Quill
    const quillEditors = document.querySelectorAll('.ql-editor');
    const textareas = document.querySelectorAll('textarea.writer-editor');
    
    if (quillEditors.length === 0 || textareas.length === 0) {
        console.log('AutoSave: No Quill editors found, retrying in 1s...');
        setTimeout(initializeAutoSave, 1000);
        return;
    }

    console.log(`AutoSave: Found ${quillEditors.length} Quill editor(s)`);

    // Для каждого редактора настраиваем автосейв
    quillEditors.forEach((quillEditor, index) => {
        const textarea = textareas[index];
        if (!textarea) return;

        setupAutoSaveForEditor(quillEditor, textarea, index);
    });
}

function setupAutoSaveForEditor(quillEditor, textarea, editorIndex) {
    let saveTimeout;
    let isSaving = false;
    let lastSavedContent = textarea.value;
    let changeCount = 0;

    // Получаем экземпляр Quill из контейнера
    const quillContainer = quillEditor.closest('.ql-container');
    const quillInstance = quillContainer ? Quill.find(quillContainer) : null;
    
    if (!quillInstance) {
        console.error(`AutoSave: Could not find Quill instance for editor ${editorIndex}`);
        return;
    }

    console.log(`AutoSave: Setting up for editor ${editorIndex}`);

    function showSaveMessage(message) {
        let messageEl = document.getElementById('autosave-message');
        if (!messageEl) {
            messageEl = document.createElement('div');
            messageEl.id = 'autosave-message';
            messageEl.style.cssText = `
                position: fixed; 
                top: 70px; 
                right: 10px; 
                padding: 8px 12px; 
                background: #28a745; 
                color: white; 
                border-radius: 3px; 
                z-index: 10000; 
                font-size: 0.8rem;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            `;
            document.body.appendChild(messageEl);
        }
        
        messageEl.textContent = message;
        messageEl.style.display = 'block';
        
        setTimeout(() => {
            messageEl.style.display = 'none';
        }, 2000);
    }

    function showError(message) {
        let messageEl = document.getElementById('autosave-message');
        if (!messageEl) {
            messageEl = document.createElement('div');
            messageEl.id = 'autosave-message';
            messageEl.style.cssText = `
                position: fixed; 
                top: 70px; 
                right: 10px; 
                padding: 8px 12px; 
                background: #dc3545; 
                color: white; 
                border-radius: 3px; 
                z-index: 10000; 
                font-size: 0.8rem;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            `;
            document.body.appendChild(messageEl);
        }
        
        messageEl.textContent = message;
        messageEl.style.background = '#dc3545';
        messageEl.style.display = 'block';
        
        setTimeout(() => {
            messageEl.style.display = 'none';
            messageEl.style.background = '#28a745';
        }, 3000);
    }

    function autoSave() {
        if (isSaving) {
            console.log('AutoSave: Already saving, skipping...');
            return;
        }

        const currentContent = textarea.value;
        
        // Проверяем, изменилось ли содержимое
        if (currentContent === lastSavedContent) {
            console.log('AutoSave: No changes detected');
            return;
        }

        changeCount++;
        console.log(`AutoSave: Changes detected (${changeCount}), saving...`);

        isSaving = true;

        // Показываем индикатор сохранения
        showSaveMessage('Сохранение...');

        const formData = new FormData();
        formData.append('content', currentContent);
        
        // Добавляем title если есть
        const titleInput = document.querySelector('input[name="title"]');
        if (titleInput) {
            formData.append('title', titleInput.value);
        }
        
        // Добавляем status если есть
        const statusSelect = document.querySelector('select[name="status"]');
        if (statusSelect) {
            formData.append('status', statusSelect.value);
        }
        
        formData.append('autosave', 'true');
        formData.append('csrf_token', document.querySelector('input[name="csrf_token"]')?.value || '');

        const currentUrl = window.location.href;
        
        fetch(currentUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                lastSavedContent = currentContent;
                showSaveMessage('Автосохранено: ' + new Date().toLocaleTimeString());
                console.log('AutoSave: Successfully saved');
            } else {
                throw new Error(data.error || 'Unknown error');
            }
        })
        .catch(error => {
            console.error('AutoSave Error:', error);
            showError('Ошибка автосохранения: ' + error.message);
        })
        .finally(() => {
            isSaving = false;
        });
    }

    // Слушаем изменения в Quill редакторе
    quillInstance.on('text-change', function(delta, oldDelta, source) {
        if (source === 'user') {
            console.log('AutoSave: Text changed by user');
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(autoSave, 2000); // Сохраняем через 2 секунды после изменения
        }
    });

    // Также слушаем изменения в title и status
    const titleInput = document.querySelector('input[name="title"]');
    if (titleInput) {
        titleInput.addEventListener('input', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(autoSave, 2000);
        });
    }

    const statusSelect = document.querySelector('select[name="status"]');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(autoSave, 1000);
        });
    }

    // Предупреждение при закрытии страницы с несохраненными изменениями
    window.addEventListener('beforeunload', function(e) {
        if (textarea.value !== lastSavedContent && !isSaving) {
            e.preventDefault();
            e.returnValue = 'У вас есть несохраненные изменения. Вы уверены, что хотите уйти?';
            return e.returnValue;
        }
    });

    // Периодическое сохранение каждые 30 секунд (на всякий случай)
    setInterval(() => {
        if (textarea.value !== lastSavedContent && !isSaving) {
            console.log('AutoSave: Periodic save triggered');
            autoSave();
        }
    }, 30000);

    console.log(`AutoSave: Successfully set up for editor ${editorIndex}`);
}