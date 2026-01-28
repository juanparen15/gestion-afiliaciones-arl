/**
 * Utilidades para exportar gráficas de Filament/Chart.js
 * Soporta: PNG, JPG, PDF, Word (DOCX)
 */

(function() {
    'use strict';

    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => setTimeout(initChartExport, 800));
    } else {
        setTimeout(initChartExport, 800);
    }

    // Re-inicializar después de navegación Livewire
    document.addEventListener('livewire:navigated', () => setTimeout(initChartExport, 800));

    // Re-inicializar cuando Livewire actualiza componentes
    document.addEventListener('livewire:initialized', () => {
        Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
            succeed(() => {
                setTimeout(initChartExport, 500);
            });
        });
    });

    // Observar cambios en el DOM para detectar nuevos widgets
    const observer = new MutationObserver((mutations) => {
        let shouldInit = false;
        mutations.forEach(mutation => {
            if (mutation.addedNodes.length > 0) {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1 && (node.querySelector('canvas') || node.tagName === 'CANVAS')) {
                        shouldInit = true;
                    }
                });
            }
        });
        if (shouldInit) {
            setTimeout(initChartExport, 300);
        }
    });

    observer.observe(document.body, { childList: true, subtree: true });

    function initChartExport() {
        // Buscar todos los canvas de Chart.js
        const canvases = document.querySelectorAll('canvas');

        canvases.forEach(canvas => {
            // Buscar el contenedor del widget (subir en el DOM)
            const widget = findWidgetContainer(canvas);
            if (!widget) return;

            // Evitar agregar múltiples botones
            if (widget.querySelector('.chart-download-btn')) return;

            // Obtener título del widget
            const title = getWidgetTitle(widget);

            // Buscar el header del widget para agregar el botón
            const header = findWidgetHeader(widget);
            if (!header) return;

            // Crear y agregar botón de descarga
            const downloadBtn = createDownloadButton(widget, canvas, title);

            // Buscar si hay un contenedor de acciones existente
            let actionsContainer = header.querySelector('.flex.items-center.gap-x-3, .fi-ta-header-toolbar');

            if (!actionsContainer) {
                // Crear contenedor de acciones
                actionsContainer = document.createElement('div');
                actionsContainer.className = 'flex items-center gap-x-3 ms-auto';
                header.appendChild(actionsContainer);
            }

            actionsContainer.appendChild(downloadBtn);
        });
    }

    function findWidgetContainer(canvas) {
        let element = canvas.parentElement;
        let maxLevels = 10;

        while (element && maxLevels > 0) {
            // Buscar clases comunes de widgets de Filament
            if (element.classList.contains('fi-wi-chart') ||
                element.classList.contains('filament-widget') ||
                element.hasAttribute('wire:id') ||
                element.classList.contains('fi-wi')) {
                return element;
            }
            element = element.parentElement;
            maxLevels--;
        }

        // Si no encontramos un widget específico, usar el contenedor más cercano con wire:id
        element = canvas.parentElement;
        maxLevels = 10;
        while (element && maxLevels > 0) {
            if (element.hasAttribute('wire:id')) {
                return element;
            }
            element = element.parentElement;
            maxLevels--;
        }

        return null;
    }

    function findWidgetHeader(widget) {
        // Buscar diferentes estructuras de header
        const selectors = [
            '.fi-wi-chart-header',
            '.fi-section-header',
            '[class*="header"]',
            '.flex.items-center.justify-between',
            'header',
            '.p-4:first-child',
            '.p-6:first-child'
        ];

        for (const selector of selectors) {
            const header = widget.querySelector(selector);
            if (header && !header.querySelector('canvas')) {
                return header;
            }
        }

        // Si no encontramos header, buscar el primer div que contenga texto (heading)
        const headingEl = widget.querySelector('h3, h2, [class*="heading"], [class*="title"]');
        if (headingEl) {
            return headingEl.parentElement;
        }

        return null;
    }

    function getWidgetTitle(widget) {
        const selectors = [
            '.fi-wi-chart-heading',
            '.fi-section-heading-title',
            'h3',
            'h2',
            '[class*="heading"]',
            '[class*="title"]'
        ];

        for (const selector of selectors) {
            const el = widget.querySelector(selector);
            if (el && el.textContent.trim()) {
                return el.textContent.trim();
            }
        }

        return 'Gráfica';
    }

    function createDownloadButton(widget, canvas, title) {
        const container = document.createElement('div');
        container.className = 'chart-download-btn relative';

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'fi-icon-btn relative flex items-center justify-center rounded-lg outline-none transition duration-75 focus-visible:ring-2 disabled:pointer-events-none disabled:opacity-70 h-9 w-9 text-gray-400 hover:text-gray-500 focus-visible:ring-primary-500/50 dark:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-50 dark:hover:bg-white/5';
        btn.title = 'Descargar gráfica';
        btn.innerHTML = `
            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
        `;

        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            showExportMenu(btn, canvas, title);
        });

        container.appendChild(btn);
        return container;
    }

    function showExportMenu(btn, canvas, title) {
        // Remover menú existente si hay uno
        const existingMenu = document.getElementById('chart-export-menu');
        if (existingMenu) {
            existingMenu.remove();
        }

        const menu = document.createElement('div');
        menu.id = 'chart-export-menu';
        menu.className = 'fixed z-[9999] w-48 rounded-lg bg-white dark:bg-gray-900 shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10';
        menu.style.animation = 'chartMenuFadeIn 0.15s ease-out';

        menu.innerHTML = `
            <div class="p-1" role="menu">
                <button class="export-option flex items-center gap-3 w-full rounded-md px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors" data-format="png">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>Imagen PNG</span>
                </button>
                <button class="export-option flex items-center gap-3 w-full rounded-md px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors" data-format="jpg">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>Imagen JPG</span>
                </button>
                <div class="my-1 border-t border-gray-200 dark:border-white/10"></div>
                <button class="export-option flex items-center gap-3 w-full rounded-md px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors" data-format="pdf">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <span>Documento PDF</span>
                </button>
                <button class="export-option flex items-center gap-3 w-full rounded-md px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors" data-format="docx">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>Documento Word</span>
                </button>
            </div>
        `;

        // Posicionar el menú
        const rect = btn.getBoundingClientRect();
        const menuHeight = 200;
        const spaceBelow = window.innerHeight - rect.bottom;

        menu.style.left = Math.max(10, rect.right - 192) + 'px';

        if (spaceBelow < menuHeight && rect.top > menuHeight) {
            menu.style.top = (rect.top - menuHeight - 5) + 'px';
        } else {
            menu.style.top = (rect.bottom + 5) + 'px';
        }

        document.body.appendChild(menu);

        // Manejar clics en opciones
        menu.querySelectorAll('.export-option').forEach(option => {
            option.addEventListener('click', function() {
                const format = this.dataset.format;
                exportChart(canvas, title, format);
                menu.remove();
            });
        });

        // Cerrar menú al hacer clic fuera
        const closeHandler = function(e) {
            if (!menu.contains(e.target) && !btn.contains(e.target)) {
                menu.remove();
                document.removeEventListener('click', closeHandler);
            }
        };
        setTimeout(() => document.addEventListener('click', closeHandler), 50);

        // Cerrar con Escape
        const escHandler = function(e) {
            if (e.key === 'Escape') {
                menu.remove();
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
    }

    async function exportChart(canvas, title, format) {
        const filename = title.toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]/g, '_')
            .replace(/_+/g, '_')
            .replace(/^_|_$/g, '');

        const date = new Date().toISOString().split('T')[0];

        showNotification('Generando archivo...', 'info');

        try {
            switch (format) {
                case 'png':
                    await exportToPNG(canvas, `${filename}_${date}.png`);
                    break;
                case 'jpg':
                    await exportToJPG(canvas, `${filename}_${date}.jpg`);
                    break;
                case 'pdf':
                    await exportToPDF(canvas, title, `${filename}_${date}.pdf`);
                    break;
                case 'docx':
                    await exportToWord(canvas, title, `${filename}_${date}.docx`);
                    break;
            }
            showNotification('Descarga completada', 'success');
        } catch (error) {
            console.error('Error al exportar:', error);
            showNotification('Error: ' + error.message, 'error');
        }
    }

    async function exportToPNG(canvas, filename) {
        const tempCanvas = createExportCanvas(canvas);
        const dataUrl = tempCanvas.toDataURL('image/png', 1.0);
        downloadDataUrl(dataUrl, filename);
    }

    async function exportToJPG(canvas, filename) {
        const tempCanvas = createExportCanvas(canvas, '#ffffff');
        const dataUrl = tempCanvas.toDataURL('image/jpeg', 0.95);
        downloadDataUrl(dataUrl, filename);
    }

    function createExportCanvas(sourceCanvas, bgColor = null) {
        const padding = 20;
        const tempCanvas = document.createElement('canvas');
        tempCanvas.width = sourceCanvas.width + (padding * 2);
        tempCanvas.height = sourceCanvas.height + (padding * 2);
        const ctx = tempCanvas.getContext('2d');

        if (bgColor) {
            ctx.fillStyle = bgColor;
            ctx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
        }

        ctx.drawImage(sourceCanvas, padding, padding);
        return tempCanvas;
    }

    async function exportToPDF(canvas, title, filename) {
        if (typeof jspdf === 'undefined' && typeof jsPDF === 'undefined') {
            await loadScript('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js');
        }

        const { jsPDF } = window.jspdf || window;
        const pdf = new jsPDF({
            orientation: 'landscape',
            unit: 'mm',
            format: 'a4'
        });

        // Header
        pdf.setFontSize(18);
        pdf.setFont(undefined, 'bold');
        pdf.setTextColor(51, 51, 51);
        pdf.text(title, 15, 20);

        pdf.setFontSize(10);
        pdf.setFont(undefined, 'normal');
        pdf.setTextColor(128, 128, 128);
        const dateStr = new Date().toLocaleDateString('es-CO', {
            year: 'numeric', month: 'long', day: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });
        pdf.text('Generado: ' + dateStr, 15, 28);

        pdf.setDrawColor(200, 200, 200);
        pdf.line(15, 32, 282, 32);

        // Imagen
        const imgData = canvas.toDataURL('image/png', 1.0);
        const imgWidth = 260;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        pdf.addImage(imgData, 'PNG', 18, 38, imgWidth, Math.min(imgHeight, 155));

        // Footer
        pdf.setFontSize(8);
        pdf.setTextColor(150, 150, 150);
        pdf.text('Sistema de Gestión de Afiliaciones ARL', 15, 200);

        pdf.save(filename);
    }

    async function exportToWord(canvas, title, filename) {
        if (typeof docx === 'undefined') {
            await loadScript('https://cdnjs.cloudflare.com/ajax/libs/docx/8.2.2/docx.umd.min.js');
        }

        const { Document, Packer, Paragraph, TextRun, ImageRun, HeadingLevel, AlignmentType, BorderStyle } = docx;

        const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/png', 1.0));
        const arrayBuffer = await blob.arrayBuffer();

        const dateStr = new Date().toLocaleDateString('es-CO', {
            year: 'numeric', month: 'long', day: 'numeric',
            hour: '2-digit', minute: '2-digit'
        });

        const doc = new Document({
            sections: [{
                properties: {
                    page: { margin: { top: 720, right: 720, bottom: 720, left: 720 } }
                },
                children: [
                    new Paragraph({
                        text: title,
                        heading: HeadingLevel.HEADING_1,
                        alignment: AlignmentType.CENTER,
                        spacing: { after: 200 }
                    }),
                    new Paragraph({
                        children: [
                            new TextRun({
                                text: 'Generado: ' + dateStr,
                                size: 20, color: '666666', italics: true
                            })
                        ],
                        alignment: AlignmentType.CENTER,
                        spacing: { after: 400 }
                    }),
                    new Paragraph({
                        children: [
                            new ImageRun({
                                data: arrayBuffer,
                                transformation: {
                                    width: 580,
                                    height: Math.round((canvas.height * 580) / canvas.width)
                                },
                                type: 'png'
                            })
                        ],
                        alignment: AlignmentType.CENTER
                    }),
                    new Paragraph({ text: '', spacing: { before: 400 } }),
                    new Paragraph({
                        children: [
                            new TextRun({
                                text: 'Sistema de Gestión de Afiliaciones ARL',
                                size: 18, color: '999999'
                            })
                        ],
                        alignment: AlignmentType.CENTER,
                        border: {
                            top: { color: 'CCCCCC', space: 10, style: BorderStyle.SINGLE, size: 6 }
                        }
                    })
                ]
            }]
        });

        const docBlob = await Packer.toBlob(doc);
        downloadBlob(docBlob, filename);
    }

    function downloadDataUrl(dataUrl, filename) {
        const link = document.createElement('a');
        link.href = dataUrl;
        link.download = filename;
        link.click();
    }

    function downloadBlob(blob, filename) {
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        link.click();
        URL.revokeObjectURL(url);
    }

    function loadScript(src) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${src}"]`)) {
                // Esperar un poco para que el script se cargue completamente
                setTimeout(resolve, 100);
                return;
            }
            const script = document.createElement('script');
            script.src = src;
            script.onload = () => setTimeout(resolve, 100);
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    function showNotification(message, type = 'info') {
        // Crear contenedor si no existe
        let container = document.getElementById('chart-notifications');
        if (!container) {
            container = document.createElement('div');
            container.id = 'chart-notifications';
            container.style.cssText = 'position:fixed;top:16px;right:16px;z-index:99999;display:flex;flex-direction:column;gap:12px;max-width:380px;';
            document.body.appendChild(container);
        }

        // Configuración por tipo
        const config = {
            success: {
                icon: '<svg style="width:24px;height:24px;color:#22c55e" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                title: 'Completado',
                color: '#22c55e'
            },
            error: {
                icon: '<svg style="width:24px;height:24px;color:#ef4444" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>',
                title: 'Error',
                color: '#ef4444'
            },
            info: {
                icon: '<svg style="width:24px;height:24px;color:#3b82f6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>',
                title: 'Información',
                color: '#3b82f6'
            },
            warning: {
                icon: '<svg style="width:24px;height:24px;color:#f59e0b" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>',
                title: 'Advertencia',
                color: '#f59e0b'
            }
        };

        const cfg = config[type] || config.info;

        // Crear notificación
        const notification = document.createElement('div');
        notification.style.cssText = `
            display:flex;
            gap:12px;
            align-items:flex-start;
            padding:16px;
            background:white;
            border-radius:12px;
            box-shadow:0 10px 25px -5px rgba(0,0,0,0.1),0 8px 10px -6px rgba(0,0,0,0.1);
            border:1px solid rgba(0,0,0,0.05);
            animation:notifySlideIn 0.3s ease-out;
            min-width:300px;
        `;

        notification.innerHTML = `
            <div style="flex-shrink:0">${cfg.icon}</div>
            <div style="flex:1;min-width:0">
                <div style="font-weight:600;font-size:14px;color:#111827;margin-bottom:2px">${cfg.title}</div>
                <div style="font-size:14px;color:#6b7280">${message}</div>
            </div>
            <button type="button" style="flex-shrink:0;background:none;border:none;cursor:pointer;padding:4px;color:#9ca3af;margin:-4px -4px 0 0" onmouseover="this.style.color='#6b7280'" onmouseout="this.style.color='#9ca3af'">
                <svg style="width:20px;height:20px" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        `;

        // Botón cerrar
        notification.querySelector('button').onclick = function() {
            closeNotification(notification);
        };

        container.appendChild(notification);

        // Auto-cerrar
        const duration = type === 'info' ? 2500 : 4000;
        setTimeout(() => closeNotification(notification), duration);

        function closeNotification(el) {
            if (!el || !el.parentNode) return;
            el.style.animation = 'notifySlideOut 0.3s ease-out forwards';
            setTimeout(() => el.remove(), 300);
        }
    }

    // Agregar estilos
    const style = document.createElement('style');
    style.textContent = `
        @keyframes chartMenuFadeIn {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes notifySlideIn {
            from { opacity: 0; transform: translateX(100%); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes notifySlideOut {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(100%); }
        }
    `;
    document.head.appendChild(style);

})();
