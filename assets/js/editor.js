class DialogueFormatter {
    constructor(quill) {
        this.quill = quill;
        this.setupKeyboardHandling();
    }

    setupKeyboardHandling() {
        this.quill.keyboard.addBinding({
            key: ' ',
            shortKey: false,
            handler: (range, context) => {
                return this.handleSpacePress(range, context);
            }
        });
    }

    handleSpacePress(range, context) {
        // Проверяем, достаточно ли символов для проверки
        if (range.index < 2) return true;
        
        // Получаем текст от начала строки до текущей позиции
        const lineStart = this.getLineStart(range.index);
        const textBeforeCursor = this.quill.getText(lineStart, range.index - lineStart);
        
        // Проверяем, начинается ли строка с "-" и пробел будет первым пробелом после дефиса
        if (this.isBeginningOfLine(lineStart, range.index) && 
            textBeforeCursor === '-') {
            
            // Сохраняем текущую позицию курсора
            const savedPosition = range.index;
            
            // Заменяем дефис на тире, сохраняя пробел
            this.quill.deleteText(range.index - 1, 2, 'user');
            this.quill.insertText(range.index - 1, '— ', 'user');
            
            // Восстанавливаем курсор после пробела
            setTimeout(() => {
                this.quill.setSelection(savedPosition + 2, 0, 'silent');
            }, 0);
            
            return true; // Разрешаем стандартную обработку пробела
        }
        
        return true;
    }

    getLineStart(index) {
        let lineStart = index;
        while (lineStart > 0 && this.quill.getText(lineStart - 1, 1) !== '\n') {
            lineStart--;
        }
        return lineStart;
    }

    isBeginningOfLine(lineStart, currentIndex) {
        return currentIndex === lineStart + 1;
    }
}

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
                    [{ 'align': [] }],
                    [{ 'color': [] }, { 'background': [] }], 
                    ['blockquote','code-block'],
                    [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'size': ['small', false, 'large', 'huge'] }],
                    [{ 'font': [] }],
                    ['link','image','video'],
                    ['clean'],
                ],
                history: { delay: 1000, maxStack: 100, userOnly: true },
                keyboard: {
                    bindings: {
                        // Отключаем автоформатирование списков для дефиса с пробелом
                        'list autofill': {
                            key: ' ',
                            format: ['list'],
                            handler: function(range, context) {
                                // Если это начало строки с дефисом - не создаем список
                                if (context.prefix && context.prefix.trim() === '-') {
                                    return true; // Пропускаем автоформатирование
                                }
                                // Стандартная обработка для других случаев
                                return Quill.import('modules/keyboard').bindings['list autofill'].handler.call(this, range, context);
                            }
                        }
                    }
                }
            },
            placeholder: 'Введите текст главы...'
        });

        // Инициализируем автоформатер диалогов
        new DialogueFormatter(this.quill);

        // Загружаем текст
        const rawContent = this.editorContainer.dataset.content || '';
        if (rawContent.trim()) this.quill.root.innerHTML = rawContent.trim();

        // Обрабатываем уже существующие диалоги при загрузке
        setTimeout(() => this.formatExistingDialogues(), 100);

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

    // Форматирование уже существующих диалогов при загрузке
    formatExistingDialogues() {
        const text = this.quill.getText();
        const lines = text.split('\n');
        
        let totalOffset = 0;
        lines.forEach((line, index) => {
            if (line.startsWith('- ')) {
                // Находим позицию для замены
                const replacePosition = totalOffset;
                
                // Заменяем дефис на тире
                this.quill.deleteText(replacePosition, 1, 'silent');
                this.quill.insertText(replacePosition, '—', 'silent');
            }
            
            totalOffset += line.length + 1; // +1 для символа новой строки
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.writerEditor = new WriterEditor();
});