document.addEventListener('DOMContentLoaded', function() {
    const contentTextarea = document.getElementById('content');
    const previewForm = document.getElementById('preview-form');
    
    if (!contentTextarea) return;
    
    let isFullscreen = false;
    let originalStyles = {};
    let isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    
    initEditor();
    
    function initEditor() {
        autoResize();
        contentTextarea.addEventListener('input', autoResize);
        contentTextarea.addEventListener('input', processDialogues);
        contentTextarea.addEventListener('keydown', handleTab);
        contentTextarea.addEventListener('input', updatePreviewContent);
        
        updatePreviewContent();
        addControlButtons();
        
        // На мобильных устройствах добавляем обработчик изменения ориентации
        if (isMobile) {
            window.addEventListener('orientationchange', function() {
                if (isFullscreen) {
                    setTimeout(adjustForMobileKeyboard, 300);
                }
            });
            
            // Обработчик для виртуальной клавиатуры
            window.addEventListener('resize', function() {
                if (isFullscreen && isMobile) {
                    adjustForMobileKeyboard();
                }
            });
        }
    }
    
    function autoResize() {
        if (isFullscreen) return;
        
        contentTextarea.style.height = 'auto';
        contentTextarea.style.height = contentTextarea.scrollHeight + 'px';
    }
    
    function processDialogues() {
        const lines = contentTextarea.value.split('\n');
        let changed = false;
        
        const processedLines = lines.map(line => {
            if (line.trim().startsWith('- ') && line.trim().length > 2) {
                const trimmed = line.trim();
                const restOfLine = trimmed.substring(2);
                if (/^[a-zA-Zа-яА-Я]/.test(restOfLine)) {
                    changed = true;
                    return line.replace(trimmed, `— ${restOfLine}`);
                }
            }
            return line;
        });
        
        if (changed) {
            const cursorPos = contentTextarea.selectionStart;
            contentTextarea.value = processedLines.join('\n');
            contentTextarea.setSelectionRange(cursorPos, cursorPos);
            if (!isFullscreen) autoResize();
        }
    }
    
    function handleTab(e) {
        if (e.key === 'Tab') {
            e.preventDefault();
            const start = contentTextarea.selectionStart;
            const end = contentTextarea.selectionEnd;
            
            contentTextarea.value = contentTextarea.value.substring(0, start) + '    ' + contentTextarea.value.substring(end);
            contentTextarea.selectionStart = contentTextarea.selectionEnd = start + 4;
            if (!isFullscreen) autoResize();
        }
    }
    
    function updatePreviewContent() {
        if (previewForm) {
            document.getElementById('preview-content').value = contentTextarea.value;
        }
    }
    
    function adjustForMobileKeyboard() {
        if (!isMobile || !isFullscreen) return;
        
        // На мобильных устройствах уменьшаем высоту textarea, чтобы клавиатура не перекрывала контент
        const viewportHeight = window.innerHeight;
        const keyboardHeight = viewportHeight * 0.4; // Предполагаемая высота клавиатуры (40% экрана)
        const availableHeight = viewportHeight - keyboardHeight - 80; // 80px для кнопок и отступов
        
        contentTextarea.style.height = availableHeight + 'px';
        contentTextarea.style.paddingBottom = '20px';
        
        // Прокручиваем к курсору
        setTimeout(() => {
            const cursorPos = contentTextarea.selectionStart;
            if (cursorPos > 0) {
                scrollToCursor();
            }
        }, 100);
    }
    
    function scrollToCursor() {
        const textarea = contentTextarea;
        const cursorPos = textarea.selectionStart;
        
        // Создаем временный элемент для измерения позиции курсора
        const tempDiv = document.createElement('div');
        tempDiv.style.cssText = `
            position: absolute;
            top: -1000px;
            left: -1000px;
            width: ${textarea.clientWidth}px;
            padding: ${textarea.style.padding};
            font: ${getComputedStyle(textarea).font};
            line-height: ${textarea.style.lineHeight};
            white-space: pre-wrap;
            word-wrap: break-word;
            visibility: hidden;
        `;
        
        const textBeforeCursor = textarea.value.substring(0, cursorPos);
        tempDiv.textContent = textBeforeCursor;
        
        document.body.appendChild(tempDiv);
        const textHeight = tempDiv.offsetHeight;
        document.body.removeChild(tempDiv);
        
        // Прокручиваем так, чтобы курсор был виден
        const lineHeight = parseInt(getComputedStyle(textarea).lineHeight) || 24;
        const visibleHeight = textarea.clientHeight;
        const cursorLine = Math.floor(textHeight / lineHeight);
        const visibleLines = Math.floor(visibleHeight / lineHeight);
        
        const targetScroll = Math.max(0, (cursorLine - Math.floor(visibleLines / 3)) * lineHeight);
        
        textarea.scrollTop = targetScroll;
    }
    
    function addControlButtons() {
        const container = contentTextarea.parentElement;
        
        const controlsContainer = document.createElement('div');
        controlsContainer.className = 'editor-controls';
        
        const fullscreenBtn = createButton('⛶', 'Полноэкранный режим', toggleFullscreen);
        const helpBtn = createButton('❓', 'Справка по Markdown', showHelp);
        
        controlsContainer.appendChild(fullscreenBtn);
        controlsContainer.appendChild(helpBtn);
        
        container.insertBefore(controlsContainer, contentTextarea);
        
        function toggleFullscreen() {
            if (!isFullscreen) {
                enterFullscreen();
            } else {
                exitFullscreen();
            }
        }
        
        function enterFullscreen() {
            originalStyles = {
                position: contentTextarea.style.position,
                top: contentTextarea.style.top,
                left: contentTextarea.style.left,
                width: contentTextarea.style.width,
                height: contentTextarea.style.height,
                zIndex: contentTextarea.style.zIndex,
                backgroundColor: contentTextarea.style.backgroundColor,
                border: contentTextarea.style.border,
                borderRadius: contentTextarea.style.borderRadius,
                fontSize: contentTextarea.style.fontSize,
                padding: contentTextarea.style.padding,
                margin: contentTextarea.style.margin
            };
            
            if (isMobile) {
                // Для мобильных - адаптивный режим с учетом клавиатуры
                const viewportHeight = window.innerHeight;
                const availableHeight = viewportHeight - 100; // Оставляем место для кнопок
                
                Object.assign(contentTextarea.style, {
                    position: 'fixed',
                    top: '50px',
                    left: '0',
                    width: '100vw',
                    height: availableHeight + 'px',
                    zIndex: '9998',
                    backgroundColor: 'white',
                    border: '2px solid #007bff',
                    borderRadius: '0',
                    fontSize: '18px',
                    padding: '15px',
                    margin: '0',
                    boxSizing: 'border-box',
                    resize: 'none'
                });
                
                // На мобильных устройствах фокусируем textarea сразу
                setTimeout(() => {
                    contentTextarea.focus();
                }, 300);
            } else {
                // Для ПК - классический полноэкранный режим
                Object.assign(contentTextarea.style, {
                    position: 'fixed',
                    top: '5vh',
                    left: '5vw',
                    width: '90vw',
                    height: '90vh',
                    zIndex: '9998',
                    backgroundColor: 'white',
                    border: '2px solid #007bff',
                    borderRadius: '8px',
                    fontSize: '16px',
                    padding: '20px',
                    margin: '0',
                    boxSizing: 'border-box',
                    resize: 'none'
                });
            }
            
            controlsContainer.style.display = 'none';
            createFullscreenControls();
            
            isFullscreen = true;
            document.body.style.overflow = 'hidden';
        }
        
        function exitFullscreen() {
            Object.assign(contentTextarea.style, originalStyles);
            
            controlsContainer.style.display = 'flex';
            removeFullscreenControls();
            
            isFullscreen = false;
            document.body.style.overflow = '';
            
            autoResize();
        }
        
        function createFullscreenControls() {
            const fullscreenControls = document.createElement('div');
            fullscreenControls.id = 'fullscreen-controls';
            
            const exitBtn = createButton('❌', 'Выйти из полноэкранного режима', exitFullscreen);
            const helpBtnFullscreen = createButton('❓', 'Справка по Markdown', showHelp);
            
            // Для мобильных увеличиваем кнопки и добавляем отступы
            const buttonSize = isMobile ? '60px' : '50px';
            const fontSize = isMobile ? '24px' : '20px';
            const topPosition = isMobile ? '10px' : '15px';
            
            [exitBtn, helpBtnFullscreen].forEach(btn => {
                btn.style.cssText = `
                    width: ${buttonSize};
                    height: ${buttonSize};
                    border-radius: 50%;
                    border: 1px solid #ddd;
                    background: white;
                    cursor: pointer;
                    font-size: ${fontSize};
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
                    transition: all 0.3s ease;
                    color: #333333;
                    touch-action: manipulation;
                `;
            });
            
            fullscreenControls.appendChild(helpBtnFullscreen);
            fullscreenControls.appendChild(exitBtn);
            
            fullscreenControls.style.cssText = `
                position: fixed;
                top: ${topPosition};
                right: 10px;
                z-index: 9999;
                display: flex;
                gap: 5px;
            `;
            
            document.body.appendChild(fullscreenControls);
            
            // Предотвращаем всплытие событий от кнопок к textarea
            fullscreenControls.addEventListener('touchstart', function(e) {
                e.stopPropagation();
            });
            
            fullscreenControls.addEventListener('touchend', function(e) {
                e.stopPropagation();
            });
        }
        
        function removeFullscreenControls() {
            const fullscreenControls = document.getElementById('fullscreen-controls');
            if (fullscreenControls) {
                fullscreenControls.remove();
            }
        }
        
        // Выход по ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isFullscreen) {
                exitFullscreen();
            }
        });
        
        // На мобильных устройствах добавляем обработчик для выхода по тапу вне textarea
        if (isMobile) {
            document.addEventListener('touchstart', function(e) {
                if (isFullscreen && !contentTextarea.contains(e.target) && 
                    !document.getElementById('fullscreen-controls')?.contains(e.target)) {
                    exitFullscreen();
                }
            });
        }
        
        // Обработчик фокуса для мобильных устройств
        if (isMobile) {
            contentTextarea.addEventListener('focus', function() {
                if (isFullscreen) {
                    setTimeout(adjustForMobileKeyboard, 100);
                }
            });
        }
    }
    
    function createButton(icon, title, onClick) {
        const button = document.createElement('button');
        button.innerHTML = icon;
        button.title = title;
        button.type = 'button';
        
        const buttonSize = isMobile ? '50px' : '40px';
        const fontSize = isMobile ? '20px' : '16px';
        
        button.style.cssText = `
            width: ${buttonSize};
            height: ${buttonSize};
            border-radius: 50%;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            font-size: ${fontSize};
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            color: #333333;
            touch-action: manipulation;
        `;
        
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.1)';
            this.style.backgroundColor = '#f8f9fa';
            this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.3)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
            this.style.backgroundColor = 'white';
            this.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
        });
        
        button.addEventListener('click', onClick);
        
        // Для мобильных устройств
        button.addEventListener('touchstart', function(e) {
            e.stopPropagation();
            this.style.transform = 'scale(1.1)';
            this.style.backgroundColor = '#f8f9fa';
            this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.3)';
        });
        
        button.addEventListener('touchend', function(e) {
            e.stopPropagation();
            this.style.transform = 'scale(1)';
            this.style.backgroundColor = 'white';
            this.style.boxShadow = '0 2px 5px rgba(0,0,0,0.2)';
            onClick();
        });
        
        return button;
    }

    function showHelp() {
        const helpContent = `
        <div style="font-family: system-ui, sans-serif; line-height: 1.6; color: #333;">
            <h1 style="color: #007bff; margin-top: 0; border-bottom: 2px solid #007bff; padding-bottom: 10px;">Справка по Markdown</h1>
            
            <div style="margin-bottom: 20px;">
                <h2 style="color: #555;">Основное форматирование</h2>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff;">
                    <p><strong>Жирный текст:</strong> **текст** или __текст__</p>
                    <p><em>Наклонный текст:</em> *текст* или _текст_</p>
                    <p><u>Подчеркнутый текст:</u> &lt;u&gt;текст&lt;/u&gt;</p>
                    <p><del>Зачеркнутый текст:</del> ~~текст~~</p>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <h2 style="color: #555;">Заголовки</h2>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;">
                    <h1 style="margin: 10px 0; font-size: 1.5em;">Заголовок 1 (# Заголовок)</h1>
                    <h2 style="margin: 10px 0; font-size: 1.3em;">Заголовок 2 (## Заголовок)</h2>
                    <h3 style="margin: 10px 0; font-size: 1.1em;">Заголовок 3 (### Заголовок)</h3>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <h2 style="color: #555;">Цитаты</h2>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;">
                    <blockquote style="margin: 0; padding-left: 15px; border-left: 3px solid #ddd; color: #666;">
                        > Это цитата
                    </blockquote>
                    <blockquote style="margin: 10px 0 0 20px; padding-left: 15px; border-left: 3px solid #ddd; color: #666;">
                        > > Вложенная цитата
                    </blockquote>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <h2 style="color: #555;">Диалоги</h2>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545;">
                    <p><strong>Автоматическое преобразование:</strong></p>
                    <p><code>- Привет!</code> → <em>— Привет!</em></p>
                    <p style="font-size: 0.9em; color: #666; margin-top: 5px;">
                        Дефис в начале строки автоматически заменяется на тире с пробелом
                    </p>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <h2 style="color: #555;">Списки</h2>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #6f42c1;">
                    <p><strong>Маркированный список:</strong></p>
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li>- Элемент списка</li>
                        <li>- Другой элемент</li>
                    </ul>
                    <p><strong>Нумерованный список:</strong></p>
                    <ol style="margin: 10px 0; padding-left: 20px;">
                        <li>1. Первый элемент</li>
                        <li>2. Второй элемент</li>
                    </ol>
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <h2 style="color: #555;">Код</h2>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #fd7e14;">
                    <p><strong>Код в строке:</strong></p>
                    <p><code>\`код в строке\`</code></p>
                    <p><strong>Блок кода:</strong></p>
                    <pre style="background: #e9ecef; padding: 10px; border-radius: 3px; overflow-x: auto; margin: 10px 0;">
\`\`\`
блок кода
многострочный
\`\`\`</pre>
                </div>
            </div>

            <div style="background: #e7f3ff; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff; margin-top: 20px;">
                <p style="margin: 0; font-size: 0.9em;"><strong>💡 Подсказка:</strong> Используйте кнопку "👁️ Предпросмотр" чтобы увидеть как будет выглядеть готовый текст!</p>
            </div>
        </div>
        `;

        const modal = document.createElement('div');
        modal.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border: 2px solid #007bff;
            border-radius: 10px;
            padding: 25px;
            z-index: 10000;
            width: 90%;
            max-width: 700px;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        `;

        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '✕';
        closeBtn.title = 'Закрыть справку';
        closeBtn.style.cssText = `
            position: absolute;
            top: 15px;
            right: 15px;
            background: #ff4444;
            color: white;
            border: none;
            font-size: 18px;
            cursor: pointer;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s ease;
        `;

        closeBtn.addEventListener('mouseenter', function() {
            this.style.background = '#cc0000';
        });

        closeBtn.addEventListener('mouseleave', function() {
            this.style.background = '#ff4444';
        });

        closeBtn.addEventListener('click', function() {
            modal.remove();
            overlay.remove();
        });

        modal.innerHTML = helpContent;
        modal.appendChild(closeBtn);

        const overlay = document.createElement('div');
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
        `;

        overlay.addEventListener('click', function() {
            modal.remove();
            overlay.remove();
        });

        document.body.appendChild(overlay);
        document.body.appendChild(modal);

        const closeHandler = function(e) {
            if (e.key === 'Escape') {
                modal.remove();
                overlay.remove();
                document.removeEventListener('keydown', closeHandler);
            }
        };
        document.addEventListener('keydown', closeHandler);
    }
});