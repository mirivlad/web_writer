class DialogueFormatter {
    constructor(quill) {
        this.quill = quill;
        this.lastKey = null;
        this.setupEventListeners();
    }

    setupEventListeners() {
        this.quill.root.addEventListener('keydown', (e) => {
            this.lastKey = e.key;
        });

        this.quill.on('text-change', (delta, oldDelta, source) => {
            if (source === 'user' && this.lastKey === ' ') {
                this.checkForDialogue();
            }
        });
    }

    getLineStart(index) {
        let lineStart = index;
        // Безопасный поиск начала строки (защита от отрицательных индексов)
        while (lineStart > 0) {
            const prevChar = this.quill.getText(lineStart - 1, 1);
            if (prevChar === '\n') break;
            lineStart--;
        }
        return lineStart;
    }

    checkForDialogue() {
        const selection = this.quill.getSelection();
        // Защита от некорректных позиций курсора
        if (!selection || selection.index < 2) return;

        const lineStart = this.getLineStart(selection.index);
        const charsFromStart = selection.index - lineStart;

        // Работаем ТОЛЬКО когда:
        // 1. Ровно 2 символа от начала строки до курсора
        // 2. Эти символы - "- "
        if (charsFromStart !== 2) return;

        const text = this.quill.getText(lineStart, 2);
        if (text !== '- ') return;

        // Атомарная операция замены
        this.quill.updateContents([
            { retain: lineStart },
            { delete: 2 },          // Удаляем "- "
            { insert: '— ' }        // Вставляем "— "
        ], 'user');

        // Явно устанавливаем курсор ПОСЛЕ пробела
        this.quill.setSelection(lineStart + 2, 0, 'silent');
    }

    // Простой метод для форматирования диалогов
    formatSelectionAsDialogue() {
        const range = this.quill.getSelection();
        if (!range || range.length === 0) return;

        const selectedText = this.quill.getText(range.index, range.length);
        
        // Простая замена всех "- " на "— " в выделенном тексте
        const formattedText = selectedText.replace(/(^|\n)- /g, '$1— ');
        
        if (formattedText !== selectedText) {
            this.quill.deleteText(range.index, range.length, 'user');
            this.quill.insertText(range.index, formattedText, 'user');
            
            // Восстанавливаем выделение
            this.quill.setSelection(range.index, formattedText.length, 'silent');
        }
    }

    // Простой метод для отмены форматирования диалогов
    unformatSelectionAsDialogue() {
        const range = this.quill.getSelection();
        if (!range || range.length === 0) return;

        const selectedText = this.quill.getText(range.index, range.length);
        
        // Простая замена всех "— " на "- " в выделенном тексте
        const unformattedText = selectedText.replace(/(^|\n)— /g, '$1- ');
        
        if (unformattedText !== selectedText) {
            this.quill.deleteText(range.index, range.length, 'user');
            this.quill.insertText(range.index, unformattedText, 'user');
            
            // Восстанавливаем выделение
            this.quill.setSelection(range.index, unformattedText.length, 'silent');
        }
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
                toolbar: {
                    container: [
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        ['bold','italic','underline','strike'],
                        [{ 'align': [] }],
                        [{ 'color': [] }, { 'background': [] }], 
                        ['blockquote','code-block'],
                        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                        [{ 'script': 'sub'}, { 'script': 'super' }],
                        [{ 'indent': '-1'}, { 'indent': '+1' }],
                        ['dialogue', 'undodialogue'],
                        [{ 'size': ['small', false, 'large', 'huge'] }],
                        [{ 'font': [] }],
                        ['link','image','video'],
                        ['clean'],
                    ],
                    handlers: {
                        'dialogue': () => {
                            if (this.dialogueFormatter) {
                                this.dialogueFormatter.formatSelectionAsDialogue();
                            }
                        },
                        'undodialogue': () => {
                            if (this.dialogueFormatter) {
                                this.dialogueFormatter.unformatSelectionAsDialogue();
                            }
                        }
                    }
                },
                history: { delay: 1000, maxStack: 100, userOnly: true },
                keyboard: {
                    bindings: {
                        'list autofill': {
                            key: ' ',
                            format: ['list'],
                            handler: function(range, context) {
                                if (context.prefix && context.prefix.trim() === '-') {
                                    return true;
                                }
                                return Quill.import('modules/keyboard').bindings['list autofill'].handler.call(this, range, context);
                            }
                        }
                    }
                }
            },
            placeholder: 'Введите текст главы...'
        });

        // Добавляем кастомные кнопки в тулбар после инициализации Quill
        this.addCustomButtonsToToolbar();

        // Инициализируем автоформатер диалогов
        this.dialogueFormatter = new DialogueFormatter(this.quill);

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

    addCustomButtonsToToolbar() {
        // Находим тулбар Quill
        const toolbar = this.quill.container.previousSibling;
        if (!toolbar) return;

        // Находим кнопки по классам Quill
        const dialogueBtn = toolbar.querySelector('.ql-dialogue');
        const undoDialogueBtn = toolbar.querySelector('.ql-undodialogue');
        
        // Заменяем содержимое кнопок на наши символы
        if (dialogueBtn) {
            dialogueBtn.innerHTML = '—';
            dialogueBtn.title = 'Форматировать диалоги (—)';
            dialogueBtn.style.fontWeight = 'bold';
        }
        
        if (undoDialogueBtn) {
            undoDialogueBtn.innerHTML = '-';
            undoDialogueBtn.title = 'Убрать форматирование диалогов (-)';
            undoDialogueBtn.style.fontWeight = 'bold';
        }
    }

    formatExistingDialogues() {
        const text = this.quill.getText();
        const lines = text.split('\n');
        
        let totalOffset = 0;
        lines.forEach((line, index) => {
            if (line.startsWith('- ')) {
                const replacePosition = totalOffset;
                
                this.quill.deleteText(replacePosition, 1, 'silent');
                this.quill.insertText(replacePosition, '—', 'silent');
            }
            
            totalOffset += line.length + 1;
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.writerEditor = new WriterEditor();
});