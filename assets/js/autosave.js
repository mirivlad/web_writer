// assets/js/autosave.js
document.addEventListener('DOMContentLoaded', function() {
    const contentTextarea = document.getElementById('content');
    const titleInput = document.getElementById('title');
    const statusSelect = document.getElementById('status');
    
    // Проверяем, что это редактирование существующей главы
    const urlParams = new URLSearchParams(window.location.search);
    const isEditMode = urlParams.has('id');
    
    if (!contentTextarea || !isEditMode) {
        console.log('Автосохранение отключено: создание новой главы');
        return;
    }
    
    let saveTimeout;
    let isSaving = false;
    let lastSavedContent = contentTextarea.value;
    
    function showSaveMessage(message) {
        let messageEl = document.getElementById('autosave-message');
        if (!messageEl) {
            messageEl = document.createElement('div');
            messageEl.id = 'autosave-message';
            messageEl.style.cssText = 'position: fixed; top: 10px; right: 10px; padding: 8px 12px; background: #333; color: white; border-radius: 3px; z-index: 1000; font-size: 0.8rem;';
            document.body.appendChild(messageEl);
        }
        
        messageEl.textContent = message;
        messageEl.style.display = 'block';
        
        setTimeout(() => {
            messageEl.style.display = 'none';
        }, 1500);
    }
    
    function autoSave() {
        if (isSaving) return;
        
        const currentContent = contentTextarea.value;
        const currentTitle = titleInput ? titleInput.value : '';
        const currentStatus = statusSelect ? statusSelect.value : 'draft';
        
        if (currentContent === lastSavedContent) return;
        
        isSaving = true;
        
        const formData = new FormData();
        formData.append('content', currentContent);
        formData.append('title', currentTitle);
        formData.append('status', currentStatus);
        formData.append('autosave', 'true');
        formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                lastSavedContent = currentContent;
                showSaveMessage('Сохранено: ' + new Date().toLocaleTimeString());
            }
        })
        .catch(error => {
            console.error('Ошибка автосохранения:', error);
        })
        .finally(() => {
            isSaving = false;
        });
    }
    
    contentTextarea.addEventListener('input', function() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(autoSave, 2000);
    });
    
    if (titleInput) {
        titleInput.addEventListener('input', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(autoSave, 2000);
        });
    }
    
    if (statusSelect) {
        statusSelect.addEventListener('change', autoSave);
    }
    
    window.addEventListener('beforeunload', function(e) {
        if (contentTextarea.value !== lastSavedContent) {
            e.preventDefault();
            e.returnValue = 'У вас есть несохраненные изменения. Вы уверены, что хотите уйти?';
        }
    });
    
    //console.log('Автосохранение включено для редактирования главы');
});