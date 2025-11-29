// editor.js
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
        while (lineStart > 0) {
            const prevChar = this.quill.getText(lineStart - 1, 1);
            if (prevChar === '\n') break;
            lineStart--;
        }
        return lineStart;
    }

    checkForDialogue() {
        const selection = this.quill.getSelection();
        if (!selection || selection.index < 2) return;

        const lineStart = this.getLineStart(selection.index);
        const charsFromStart = selection.index - lineStart;

        if (charsFromStart !== 2) return;

        const text = this.quill.getText(lineStart, 2);
        if (text !== '- ') return;

        this.quill.updateContents([
            { retain: lineStart },
            { delete: 2 },
            { insert: '— ' }
        ], 'user');

        this.quill.setSelection(lineStart + 2, 0, 'silent');
    }

    formatSelectionAsDialogue() {
        const range = this.quill.getSelection();
        if (!range || range.length === 0) return;

        const selectedText = this.quill.getText(range.index, range.length);
        const formattedText = selectedText.replace(/(^|\n)- /g, '$1— ');
        
        if (formattedText !== selectedText) {
            this.quill.deleteText(range.index, range.length, 'user');
            this.quill.insertText(range.index, formattedText, 'user');
            this.quill.setSelection(range.index, formattedText.length, 'silent');
        }
    }

    unformatSelectionAsDialogue() {
        const range = this.quill.getSelection();
        if (!range || range.length === 0) return;

        const selectedText = this.quill.getText(range.index, range.length);
        const unformattedText = selectedText.replace(/(^|\n)— /g, '$1- ');
        
        if (unformattedText !== selectedText) {
            this.quill.deleteText(range.index, range.length, 'user');
            this.quill.insertText(range.index, unformattedText, 'user');
            this.quill.setSelection(range.index, unformattedText.length, 'silent');
        }
    }
}

class WriterEditor {
    constructor(formSelector = '#chapter-form', editorContainerId = 'quill-editor', textareaId = 'content') {
        this.form = document.querySelector(formSelector);
        this.editorContainer = document.getElementById(editorContainerId);
        this.textarea = document.getElementById(textareaId);
        this.isFullscreen = false;
        this.originalStyles = {};
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
                        ['fullscreen'] // Добавляем кнопку полноэкранного режима
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
                        },
                        'fullscreen': () => {
                            this.toggleFullscreen();
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

        this.addCustomButtonsToToolbar();
        this.dialogueFormatter = new DialogueFormatter(this.quill);

        const rawContent = this.editorContainer.dataset.content || '';
        if (rawContent.trim()) this.quill.root.innerHTML = rawContent.trim();

        setTimeout(() => this.formatExistingDialogues(), 100);

        const sync = () => {
            let html = this.quill.root.innerHTML;
            html = html.replace(/^(<p><br><\/p>)+/, '').replace(/(<p><br><\/p>)+$/, '');
            this.textarea.value = html;
        };

        this.quill.on('text-change', sync);
        this.form.addEventListener('submit', sync);

        // Обработчик изменения ориентации для мобильных устройств
        window.addEventListener('orientationchange', () => {
            if (this.isFullscreen) {
                setTimeout(() => this.adjustFullscreenHeight(), 300);
            }
        });

        // Обработчик изменения размера окна
        window.addEventListener('resize', () => {
            if (this.isFullscreen) {
                this.adjustFullscreenHeight();
            }
        });

        window.quillEditorInstance = this.quill;
        window.quillTextarea = this.textarea;
    }

    addCustomButtonsToToolbar() {
        const toolbar = this.quill.container.previousSibling;
        if (!toolbar) return;

        const dialogueBtn = toolbar.querySelector('.ql-dialogue');
        const undoDialogueBtn = toolbar.querySelector('.ql-undodialogue');
        const fullscreenBtn = toolbar.querySelector('.ql-fullscreen');
        
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

        if (fullscreenBtn) {
            fullscreenBtn.innerHTML = '⛶';
            fullscreenBtn.title = 'Полноэкранный режим';
        }
    }

    toggleFullscreen() {
        if (this.isFullscreen) {
            this.exitFullscreen();
        } else {
            this.enterFullscreen();
        }
    }

    enterFullscreen() {
        this.isFullscreen = true;
        
        // Сохраняем оригинальные стили
        this.originalStyles = {
            container: this.editorContainer.style.cssText,
            body: document.body.style.cssText,
            toolbar: this.quill.container.previousSibling.style.cssText
        };

        // Получаем тулбар
        const toolbar = this.quill.container.previousSibling;

        // Применяем стили для полноэкранного режима
        document.body.style.overflow = 'hidden';
        
        this.editorContainer.style.position = 'fixed';
        this.editorContainer.style.top = '0';
        this.editorContainer.style.left = '0';
        this.editorContainer.style.width = '100vw';
        this.editorContainer.style.zIndex = '9999';
        this.editorContainer.style.background = 'white';
        this.editorContainer.style.paddingTop = toolbar.offsetHeight + 'px';

        // Стили для тулбара
        toolbar.style.position = 'fixed';
        toolbar.style.top = '0';
        toolbar.style.left = '0';
        toolbar.style.width = '100%';
        toolbar.style.zIndex = '10000';
        toolbar.style.background = 'white';
        toolbar.style.borderBottom = '1px solid #ccc';

        this.adjustFullscreenHeight();

        // Добавляем обработчик ESC
        this.escapeHandler = (e) => {
            if (e.key === 'Escape') {
                this.exitFullscreen();
            }
        };
        document.addEventListener('keydown', this.escapeHandler);

        // Добавляем кнопку выхода из полноэкранного режима
        this.addFullscreenExitButton();
    }

    exitFullscreen() {
        this.isFullscreen = false;

        // Восстанавливаем оригинальные стили
        this.editorContainer.style.cssText = this.originalStyles.container || '';
        document.body.style.cssText = this.originalStyles.body || '';
        
        const toolbar = this.quill.container.previousSibling;
        if (toolbar) {
            toolbar.style.cssText = this.originalStyles.toolbar || '';
        }

        // Удаляем обработчик ESC
        if (this.escapeHandler) {
            document.removeEventListener('keydown', this.escapeHandler);
        }

        // Удаляем кнопку выхода
        this.removeFullscreenExitButton();
    }

    adjustFullscreenHeight() {
        // Корректируем высоту с учетом видимой области и возможной клавиатуры
        const visualViewport = window.visualViewport || window;
        const height = visualViewport.height || window.innerHeight;
        
        this.editorContainer.style.height = height + 'px';
        
        // Пересчитываем размеры Quill
        setTimeout(() => {
            this.quill.root.style.height = '100%';
            this.quill.root.querySelector('.ql-editor').style.height = '100%';
        }, 50);
    }

    addFullscreenExitButton() {
        // Создаем кнопку выхода из полноэкранного режима
        this.exitButton = document.createElement('button');
        this.exitButton.innerHTML = '✕';
        this.exitButton.title = 'Выйти из полноэкранного режима (ESC)';
        this.exitButton.style.cssText = `
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 10001;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        `;
        
        this.exitButton.addEventListener('click', () => {
            this.exitFullscreen();
        });

        document.body.appendChild(this.exitButton);
    }

    removeFullscreenExitButton() {
        if (this.exitButton && this.exitButton.parentNode) {
            this.exitButton.parentNode.removeChild(this.exitButton);
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