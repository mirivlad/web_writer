document.addEventListener('DOMContentLoaded', () => {
    const quill = window.quillEditorInstance;
    const textarea = window.quillTextarea;
    if (!quill || !textarea) return;

    let lastSavedContent = textarea.value;
    let saveTimeout;

    function showMessage(message, isError = false) {
        let msgEl = document.getElementById('autosave-message');
        if (!msgEl) {
            msgEl = document.createElement('div');
            msgEl.id = 'autosave-message';
            msgEl.style.cssText = `
                position: fixed;
                top: 70px;
                right: 10px;
                padding: 8px 12px;
                background: ${isError ? '#dc3545' : '#28a745'};
                color: white;
                border-radius: 3px;
                z-index: 10000;
                font-size: 0.8rem;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            `;
            document.body.appendChild(msgEl);
        }
        msgEl.textContent = message;
        msgEl.style.background = isError ? '#dc3545' : '#28a745';
        msgEl.style.display = 'block';
        setTimeout(() => msgEl.style.display = 'none', 2000);
    }

    const autoSave = () => {
        const currentContent = textarea.value;
        if (currentContent === lastSavedContent) return;

        const form = document.getElementById('chapter-form');
        const formData = new FormData(form);
        formData.append('autosave', 'true');

        showMessage('Сохранение...');

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(res => {
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        })
        .then(data => {
            if (data.success) {
                lastSavedContent = currentContent;
                showMessage('Автосохранено: ' + new Date().toLocaleTimeString());
            } else {
                throw new Error(data.error || 'Ошибка сервера');
            }
        })
        .catch(err => {
            console.error(err);
            showMessage('Ошибка автосохранения: ' + err.message, true);
        });
    };

    quill.on('text-change', () => {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(autoSave, 2000);
    });

    // Периодическая автосохранение
    setInterval(autoSave, 30000);
});
