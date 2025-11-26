class WriterEditor {
    constructor(formSelector = '#chapter-form', editorContainerId = 'quill-editor', textareaId = 'content') {
        this.form = document.querySelector(formSelector);
        this.editorContainer = document.getElementById(editorContainerId);
        this.textarea = document.getElementById(textareaId);
        this.init();
    }

    init() {
        if (!this.editorContainer || !this.textarea || !this.form) return;

        this.quill = new Quill(this.editorContainer, {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold','italic','underline','strike'],
                    ['blockquote','code-block'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    [{ 'font': [] }],
                    ['link','image','video'],
                    ['clean']
                ],
                history: { delay: 1000, maxStack: 100, userOnly: true }
            },
            placeholder: 'Введите текст главы...'
        });

        // Загружаем текст
        const rawContent = this.editorContainer.dataset.content || '';
        if (rawContent.trim()) this.quill.root.innerHTML = rawContent.trim();

        // Синхронизация с textarea
        const sync = () => {
            let html = this.quill.root.innerHTML;
            html = html.replace(/^(<p><br><\/p>)+/, '').replace(/(<p><br><\/p>)+$/, '');
            this.textarea.value = html;
        };

        this.quill.on('text-change', sync);
        this.form.addEventListener('submit', sync);

        // Делаем глобально доступным для автосейва
        window.quillEditorInstance = this.quill;
        window.quillTextarea = this.textarea;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.writerEditor = new WriterEditor();
});
