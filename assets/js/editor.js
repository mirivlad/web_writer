// assets/js/editor.js
class WriterEditor {
    constructor() {
        this.editors = [];
        this.init();
    }

    init() {
        // Инициализируем редакторы для текстовых областей с классом .writer-editor
        document.querySelectorAll('textarea.writer-editor').forEach(textarea => {
            this.initEditor(textarea);
        });
    }

    initEditor(textarea) {
        // Создаем контейнер для Quill
        const editorContainer = document.createElement('div');
        editorContainer.className = 'writer-editor-container';
        editorContainer.style.height = '500px';
        editorContainer.style.marginBottom = '20px';

        // Вставляем контейнер перед textarea
        textarea.parentNode.insertBefore(editorContainer, textarea);
        
        // Скрываем оригинальный textarea
        textarea.style.display = 'none';

        // Настройки Quill
        const quill = new Quill(editorContainer, {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'direction': 'rtl' }],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'font': [] }],
                    [{ 'align': [] }],
                    ['link', 'image', 'video'],
                    ['clean']
                ],
                history: {
                    delay: 1000,
                    maxStack: 100,
                    userOnly: true
                }
            },
            placeholder: 'Начните писать вашу главу...',
            formats: [
                'header', 'bold', 'italic', 'underline', 'strike',
                'blockquote', 'code-block', 'list', 'bullet',
                'script', 'indent', 'direction', 'size',
                'color', 'background', 'font', 'align',
                'link', 'image', 'video'
            ]
        });

        // Устанавливаем начальное содержимое
        if (textarea.value) {
            quill.root.innerHTML = textarea.value;
        }

        // Обновляем textarea при изменении содержимого
        quill.on('text-change', () => {
            textarea.value = quill.root.innerHTML;
        });

        // Сохраняем ссылку на редактор
        this.editors.push({
            quill: quill,
            textarea: textarea
        });

        return quill;
    }

    // Метод для получения HTML содержимого
    getContent(editorIndex = 0) {
        if (this.editors[editorIndex]) {
            return this.editors[editorIndex].quill.root.innerHTML;
        }
        return '';
    }

    // Метод для установки содержимого
    setContent(content, editorIndex = 0) {
        if (this.editors[editorIndex]) {
            this.editors[editorIndex].quill.root.innerHTML = content;
        }
    }
}


// Инициализация редактора при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    window.writerEditor = new WriterEditor();
});