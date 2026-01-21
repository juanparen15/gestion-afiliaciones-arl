/**
 * Tour de onboarding para el Sistema de Afiliaciones ARL
 * Usa Driver.js - https://driverjs.com
 */

document.addEventListener("DOMContentLoaded", function () {
    const pathname = window.location.pathname;

    // Determinar en qué página estamos
    const isAdminDashboard = pathname === "/admin" || pathname === "/admin/";
    const isAfiliaciones = pathname.includes("afiliacions");
    const isDependencias = pathname.includes("dependencias");
    const isAreas = pathname.includes("areas");

    // Solo ejecutar en las páginas relevantes
    if (!isAdminDashboard && !isAfiliaciones && !isDependencias && !isAreas) {
        return;
    }

    if (isAdminDashboard) {
        // Buscar el enlace del menú "Historial de Descargos" y agregar el atributo data-tour
        const menuLinks = document.querySelectorAll(".fi-fo-wizard-footer a");
        menuLinks.forEach(function (link) {
            if (
                link.textContent.includes("Guardar Afiliaciones") ||
                link.href.includes("afiliacions/create")
            ) {
                link.setAttribute("data-tour", "menu-guardar-afiliaciones");
            }
        });
    }

    // Driver.js se carga via CDN, acceder desde window
    const driverFn = window.driver.js.driver;

    // Marcar dinámicamente elementos del menú para el tour del dashboard
    if (isAdminDashboard) {
        const menuLinks = document.querySelectorAll(".fi-sidebar-nav a");
        menuLinks.forEach(function (link) {
            if (
                link.textContent.includes("Afiliaciones") ||
                link.href.includes("afiliacions")
            ) {
                link.setAttribute("data-tour", "menu-afiliaciones");
            }
            if (
                link.textContent.includes("Dependencias") ||
                link.href.includes("dependencias")
            ) {
                link.setAttribute("data-tour", "menu-dependencias");
            }
            if (
                link.textContent.includes("Áreas") ||
                link.href.includes("areas")
            ) {
                link.setAttribute("data-tour", "menu-areas");
            }
        });
    }

    // ========== TOUR PARA INICIO (DASHBOARD) ==========
    if (isAdminDashboard) {
        const tourInicio = driverFn({
            showProgress: true,
            nextBtnText: "Siguiente",
            prevBtnText: "Anterior",
            doneBtnText: "Entendido",
            progressText: "Paso {{current}} de {{total}}",
            steps: [
                {
                    popover: {
                        title: "¿Es primera vez? Bienvenido al Sistema de Afiliaciones ARL",
                        description:
                            "Este sistema te permite gestionar las afiliaciones a ARL de los contratistas. Aquí podrás registrar y hacer seguimiento de cada afiliación.",
                    },
                },
                {
                    element: "[data-tour='menu-afiliaciones']",
                    popover: {
                        title: "Gestión de Afiliaciones",
                        description:
                            "Aquí puedes ver todas las afiliaciones, crear nuevas, verificar si han sido validadas o rechazadas según corresponda.",
                        side: "right",
                    },
                },
                // {
                //     element: "[data-tour='menu-dependencias']",
                //     popover: {
                //         title: "Dependencias / Secretarías",
                //         description:
                //             "Administra las dependencias o secretarías a las que pertenecen los contratistas.",
                //         side: "right",
                //     },
                // },
                // {
                //     element: "[data-tour='menu-areas']",
                //     popover: {
                //         title: "Áreas",
                //         description:
                //             "Gestiona las áreas dentro de cada dependencia para una mejor organización.",
                //         side: "right",
                //     },
                // },
                {
                    element: "[data-tour='help-button-dashboard']",
                    popover: {
                        title: "¿Necesitas ayuda?",
                        description:
                            "Puedes ver este tutorial de nuevo en cualquier momento haciendo clic aquí.",
                        side: "bottom",
                    },
                },
                {
                    popover: {
                        title: "¡Listo para comenzar!",
                        description:
                            "Ya conoces lo básico del sistema. Dirígete a Afiliaciones para gestionar los registros.",
                    },
                },
            ],
        });

        // Guardar referencia global
        window.tourAfiliacionesInicio = tourInicio;

        // Iniciar tour automáticamente si es primera vez
        const tourInicioShown = localStorage.getItem(
            "tourAfiliacionesInicioShown",
        );
        if (!tourInicioShown) {
            setTimeout(function () {
                tourInicio.drive();
                localStorage.setItem("tourAfiliacionesInicioShown", "true");
            }, 1000);
        }
    }

    // ========== TOUR PARA LISTA DE AFILIACIONES ==========
    if (pathname.endsWith("afiliacions") || pathname.endsWith("afiliacions/")) {
        const tourList = driverFn({
            showProgress: true,
            nextBtnText: "Siguiente",
            prevBtnText: "Anterior",
            doneBtnText: "Entendido",
            progressText: "Paso {{current}} de {{total}}",
            steps: [
                {
                    popover: {
                        title: "Bienvenido a la Lista de Afiliaciones",
                        description:
                            "Aquí puedes ver todas las afiliaciones registradas en el sistema. Cada fila muestra información importante del contratista y su estado de afiliación.",
                    },
                },
                {
                    element: ".fi-ta-table",
                    popover: {
                        title: "Tabla de Afiliaciones",
                        description:
                            "Cada fila muestra: número de contrato, contratista, dependencia, valor, fechas y estado de la afiliación.",
                        side: "top",
                    },
                },
                {
                    element: ".fi-ta-header-ctn",
                    popover: {
                        title: "Filtros y Búsqueda",
                        description:
                            "Usa los filtros para encontrar afiliaciones por estado (pendiente, validado, rechazado), dependencia, área o nivel de riesgo. También puedes buscar por nombre o número de contrato.",
                        side: "bottom",
                    },
                },
                {
                    element: "[data-tour='create-button']",
                    popover: {
                        title: "Crear Nueva Afiliación",
                        description:
                            "Aquí puedes registrar una nueva afiliación del contratista.",
                        side: "bottom",
                    },
                },
                // {
                //     element: "[data-tour='import-button']",
                //     popover: {
                //         title: "Importar desde Excel",
                //         description:
                //             "Puedes importar múltiples afiliaciones desde un archivo Excel. Descarga primero la plantilla para conocer el formato requerido.",
                //         side: "bottom",
                //     },
                // },
                {
                    element: "[data-tour='help-button']",
                    popover: {
                        title: "¿Necesitas ayuda?",
                        description:
                            "Puedes ver este tutorial de nuevo en cualquier momento.",
                        side: "bottom",
                    },
                },
                {
                    popover: {
                        title: "Estados de Afiliación",
                        description:
                            "<strong>Pendiente:</strong> Esperando validación<br><strong>Validado:</strong> Afiliación confirmada en ARL<br><strong>Rechazado:</strong> Requiere correcciones",
                    },
                },
                {
                    popover: {
                        title: "¡Listo!",
                        description:
                            "Ya conoces cómo funciona la lista de afiliaciones. Explora los registros o crea uno nuevo.",
                    },
                },
            ],
        });

        // Guardar referencia global
        window.tourAfiliacionesList = tourList;

        // Iniciar tour automáticamente si es primera vez
        const tourListShown = localStorage.getItem("tourAfiliacionesListShown");
        if (!tourListShown) {
            setTimeout(function () {
                tourList.drive();
                localStorage.setItem("tourAfiliacionesListShown", "true");
            }, 1000);
        }
    }

    // ========== TOUR PARA CREAR AFILIACIÓN (WIZARD) ==========
    if (
        pathname.includes("afiliacions/create") ||
        (pathname.includes("afiliacions/") && pathname.includes("/edit"))
    ) {
        // Nombres de los pasos del wizard (deben coincidir con Filament)
        const nombresPasos = [
            "datos-del-contratista",
            "informacion-del-contrato",
            "informacion-arl",
            "informacion-adicional",
        ];

        // Variable para almacenar referencia al tour
        let tourCreate = null;

        // Función para verificar si un elemento está visible en el DOM
        const elementoEsVisible = function (selector) {
            const elemento = document.querySelector(selector);
            if (!elemento) return false;

            // Verificar si el elemento está visible (no oculto por CSS)
            const estilo = window.getComputedStyle(elemento);
            if (estilo.display === "none" || estilo.visibility === "hidden")
                return false;

            // Verificar si tiene dimensiones
            const rect = elemento.getBoundingClientRect();
            return rect.width > 0 && rect.height > 0;
        };

        // Variable para el observer
        let observadorCambios = null;

        // Función para esperar a que aparezca un elemento y luego avanzar el tour
        const esperarElementoYAvanzar = function (selector, driver) {
            // Limpiar observer anterior si existe
            if (observadorCambios) {
                observadorCambios.disconnect();
            }

            // Crear un observer para detectar cambios en el DOM
            observadorCambios = new MutationObserver(function (mutations) {
                if (elementoEsVisible(selector)) {
                    observadorCambios.disconnect();
                    observadorCambios = null;
                    // Pequeño delay para que el DOM se estabilice
                    setTimeout(function () {
                        driver.moveNext();
                    }, 300);
                }
            });

            // Observar cambios en todo el formulario
            const formulario = document.querySelector("form") || document.body;
            observadorCambios.observe(formulario, {
                childList: true,
                subtree: true,
                attributes: true,
            });
        };

        tourCreate = driverFn({
            showProgress: true,
            nextBtnText: "Siguiente",
            prevBtnText: "Anterior",
            doneBtnText: "¡Entendido!",
            progressText: "{{current}} de {{total}}",
            allowClose: true,
            overlayClickNext: false,
            stagePadding: 5,
            popoverClass: "tour-afiliaciones-popover",
            steps: [
                // ===== INTRODUCCIÓN =====
                {
                    popover: {
                        title: "Asistente de Registro de Afiliación",
                        description:
                            "Este formulario te guiará paso a paso para registrar una nueva afiliación a ARL. <br><br><strong>El proceso consta de 3 pasos sencillos:</strong><br>1. Datos del Contratista<br>2. Información del Contrato<br>3. Información ARL",
                    },
                },

                // ===== PASO 1: DATOS DEL CONTRATISTA =====
                {
                    element: ".fi-fo-wizard-header",
                    popover: {
                        title: "Barra de Progreso",
                        description:
                            "Aquí puedes ver en qué paso te encuentras. Los pasos completados se marcan con un ✓ verde.",
                        side: "bottom",
                    },
                },
                {
                    element: '[data-tour="nombre-contratista"]',
                    popover: {
                        title: "Nombre del Contratista",
                        description:
                            "Escribe el <strong>nombre completo</strong> del contratista tal como aparece en su documento de identidad.",
                        side: "bottom",
                    },
                },
                {
                    element: '[data-tour="documento"]',
                    popover: {
                        title: "Documento de Identidad",
                        description:
                            "Ingresa el número de documento. <br><br><strong>Importante:</strong> Este número es único y no puede estar registrado previamente.",
                        side: "bottom",
                    },
                },
                {
                    element: '[data-tour="fecha-nacimiento"]',
                    popover: {
                        title: "Fecha de Nacimiento",
                        description:
                            "Ingresa la fecha de nacimiento del contratista.",
                        side: "bottom",
                    },
                },
                {
                    element: '[data-tour="numero-contacto"]',
                    popover: {
                        title: "Número de Contacto",
                        description:
                            "Ingresa el número de contacto del contratista.",
                        side: "bottom",
                    },
                },
                {
                    element: '[data-tour="correo-electronico"]',
                    popover: {
                        title: "Correo Electrónico",
                        description:
                            "Ingresa el correo electrónico del contratista.",
                        side: "top",
                    },
                },
                {
                    element: '[data-tour="direccion-residencia"]',
                    popover: {
                        title: "Dirección de Residencia",
                        description:
                            "Ingresa la dirección de residencia del contratista.",
                        side: "top",
                    },
                },
                {
                    element: '[data-tour="barrio-residencia"]',
                    popover: {
                        title: "Barrio de Residencia",
                        description:
                            "Ingresa el barrio de residencia del contratista.",
                        side: "top",
                    },
                },
                {
                    element: '[data-tour="eps"]',
                    popover: {
                        title: "EPS del Contratista",
                        description: "Ingresa la EPS del contratista.",
                        side: "top",
                    },
                },
                {
                    element: '[data-tour="afp"]',
                    popover: {
                        title: "AFP del Contratista",
                        description: "Ingresa la AFP del contratista.",
                        side: "top",
                    },
                },
                {
                    element: ".fi-fo-wizard-header-step:nth-child(2)",
                    popover: {
                        title: "👆 Haz clic aquí para continuar",
                        description:
                            "Ya completaste el <strong>Paso 1</strong>.<br><br>Ahora haz clic en <strong>'Información del Contrato'</strong> en la barra de progreso para ver los campos del siguiente paso.",
                        side: "bottom",
                    },
                    onNextClick: function (element, step, options) {
                        // Verificar si el campo del paso 2 está visible (significa que hizo clic en la barra)
                        if (
                            elementoEsVisible('[data-tour="numero-contrato"]')
                        ) {
                            // El campo está visible, continuar
                            options.driver.moveNext();
                        } else {
                            // Mostrar alerta de que debe hacer clic primero
                            alert(
                                "⚠️ Primero debes hacer clic en 'Información del Contrato' en la barra de progreso para continuar.",
                            );
                        }
                    },
                },

                // ===== PASO 2: INFORMACIÓN DEL CONTRATO =====
                {
                    element: '[data-tour="numero-contrato"]',
                    popover: {
                        title: "Número de Contrato",
                        description:
                            "¡Excelente! Ahora estás en el Paso 2.<br><br>Ingresa el número único del contrato asociado al contratista.",
                        side: "top",
                    },
                },
                {
                    element: '[data-tour="dependencia"]',
                    popover: {
                        title: "Dependencia / Secretaría",
                        description:
                            "Selecciona la dependencia o secretaría a la que pertenece el contratista.",
                        side: "top",
                    },
                },
                {
                    element: '[data-tour="area"]',
                    popover: {
                        title: "Área",
                        description:
                            "Selecciona el área dentro de la dependencia correspondiente.",
                        side: "top",
                    },
                },
                {
                    element: '[data-tour="objeto-contrato"]',
                    popover: {
                        title: "Objeto del Contrato",
                        description:
                            "Describe brevemente el objeto o propósito del contrato.",
                        side: "top",
                    },
                },
                // {
                //     element: '[data-tour="documento-contrato"]',
                //     popover: {
                //         title: "Documento del Estudio Previo",
                //         description:
                //             "Sube el archivo PDF del estudio previo del contrato.<br><br><strong>Importante:</strong> Este documento es obligatorio para completar la afiliación.",
                //         side: "top",
                //     },
                // },
                {
                    element: '[data-tour="supervisor-contrato"]',
                    popover: {
                        title: "Supervisor del Contrato",
                        description:
                            "Ingresa el nombre del supervisor del contrato.",
                        side: "top",
                    },
                },
                {
                    element: '[data-tour="valor-total-contrato"]',
                    popover: {
                        title: "Valor Total del Contrato",
                        description:
                            "Ingresa el valor total acordado en el contrato.",
                        side: "top",
                    },
                },
                {
                    element: '[data-tour="honorarios-mensuales"]',
                    popover: {
                        title: "Honorarios Mensuales",
                        description:
                            "Ingresa el valor de los honorarios mensuales acordados en el contrato.",
                        side: "top",
                    },
                },
                {
                    element: '[data-tour="ibc"]',
                    popover: {
                        title: "IBC (Ingreso Base de Cotización)",
                        description:
                            "El IBC se calcula automáticamente como el 40% de los honorarios mensuales, con un mínimo de 1 SMLV.",
                        side: "top",
                    },
                },
                {
                    element: '[data-tour="meses-contrato"]',
                    popover: {
                        title: "Duración en Meses",
                        description:
                            "Ingresa la duración del contrato en meses.",
                        side: "top",
                    },
                },
                {
                    element: '[data-tour="dias-contrato"]',
                    popover: {
                        title: "Duración en Días",
                        description:
                            "Ingresa la duración adicional del contrato en días.",
                        side: "top",
                    },
                },
                {
                    element: '[data-tour="fecha-inicio"]',
                    popover: {
                        title: "Fecha de Inicio del Contrato",
                        description:
                            "Selecciona la fecha de inicio del contrato.",
                        side: "top",
                    },
                },
                {
                    element: '[data-tour="fecha-fin"]',
                    popover: {
                        title: "Fecha de Fin del Contrato",
                        description:
                            "La fecha de fin se calcula automáticamente según la duración, pero puedes editarla si es necesario.",
                        side: "top",
                    },
                },
                {
                    element: '[data-tour="documento-contrato"]',
                    popover: {
                        title: "Estudio Previo del Contrato",
                        description:
                            "Sube el archivo PDF del estudio previo.<br><br><strong>Importante:</strong> Este documento es obligatorio para completar la afiliación.",
                        side: "top",
                    },
                },
                {
                    element: ".fi-fo-wizard-header-step:nth-child(3)",
                    popover: {
                        title: "👆 Haz clic aquí para continuar",
                        description:
                            "Ya completaste el <strong>Paso 2</strong>.<br><br>Ahora haz clic en <strong>'Información ARL'</strong> en la barra de progreso para ver los campos del último paso.",
                        side: "bottom",
                    },
                    onNextClick: function (element, step, options) {
                        // Verificar si el campo del paso 3 está visible (significa que hizo clic en la barra)
                        if (elementoEsVisible('[data-tour="nombre-arl"]')) {
                            // El campo está visible, continuar
                            options.driver.moveNext();
                        } else {
                            // Mostrar alerta de que debe hacer clic primero
                            alert(
                                "⚠️ Primero debes hacer clic en 'Información ARL' en la barra de progreso para continuar.",
                            );
                        }
                    },
                },

                // ===== PASO 3: INFORMACIÓN ARL =====
                {
                    element: '[data-tour="nombre-arl"]',
                    popover: {
                        title: "Nombre de la ARL",
                        description:
                            "¡Muy bien! Este es el último paso.<br><br>Selecciona la ARL a la que se afiliará el contratista.",
                        side: "top",
                    },
                },
                {
                    element: '[data-tour="nivel-riesgo"]',
                    popover: {
                        title: "Nivel de Riesgo",
                        description:
                            "Selecciona el nivel de riesgo según las actividades del contrato:<br><br>" +
                            "• <strong>Nivel I:</strong> Riesgo Mínimo (oficina)<br>" +
                            "• <strong>Nivel II:</strong> Riesgo Bajo<br>" +
                            "• <strong>Nivel III:</strong> Riesgo Medio<br>" +
                            "• <strong>Nivel IV:</strong> Riesgo Alto<br>" +
                            "• <strong>Nivel V:</strong> Riesgo Máximo (trabajos peligrosos)",
                        side: "top",
                    },
                },
                {
                    element: '[data-tour="observaciones-arl"]',
                    popover: {
                        title: "Observaciones sobre la ARL",
                        description:
                            "Agrega cualquier observación adicional relacionada con la ARL.",
                        side: "top",
                    },
                },
                {
                    element: "[data-tour='help-button-afiliacion-create']",
                    popover: {
                        title: "¿Necesitas ayuda?",
                        description:
                            "Puedes ver este tutorial de nuevo en cualquier momento.",
                        side: "bottom",
                    },
                },

                // ===== FINALIZACIÓN =====
                {
                    // element: ".fi-fo-wizard-footer",
                    element: "[data-tour='guardar-afiliacion']",
                    // element: ".fi-form-actions",
                    popover: {
                        title: "Guardar Afiliación",
                        description:
                            "Una vez completes todos los campos, haz clic en <strong>'Crear'</strong> para registrar.<br><br>La afiliación quedará en estado <strong>'Pendiente'</strong> hasta que el equipo de Seguridad y Salud en el Trabajo la valide.",
                        side: "top",
                    },
                },
                {
                    popover: {
                        title: "¡Tutorial Completado!",
                        description:
                            "<strong>Resumen del proceso:</strong><br><br>" +
                            "1️ Ingresa datos del contratista<br>" +
                            "2️ Completa información del contrato<br>" +
                            "3️ Configura la ARL y nivel de riesgo<br>" +
                            "4️ Guarda y espera validación<br><br>" +
                            "💡 <strong>Tip:</strong> Puedes volver a ver este tour haciendo clic en el botón de ayuda.",
                    },
                    onNextClick: function (element, step, options) {
                        // Finalizar el tour
                        options.driver.destroy();
                    },
                },
            ],
        });

        window.tourAfiliacionesCreate = tourCreate;

        // Iniciar tour automáticamente si es primera vez
        const tourCreateShown = localStorage.getItem(
            "tourAfiliacionesCreateShown",
        );
        if (!tourCreateShown) {
            setTimeout(function () {
                tourCreate.drive();
                localStorage.setItem("tourAfiliacionesCreateShown", "true");
            }, 1200);
        }
    }

    // ========== TOUR PARA DEPENDENCIAS ==========
    if (
        pathname.endsWith("dependencias") ||
        pathname.endsWith("dependencias/")
    ) {
        const tourDependencias = driverFn({
            showProgress: true,
            nextBtnText: "Siguiente",
            prevBtnText: "Anterior",
            doneBtnText: "Entendido",
            progressText: "Paso {{current}} de {{total}}",
            steps: [
                {
                    popover: {
                        title: "Gestión de Dependencias",
                        description:
                            "Las dependencias (o secretarías) son las entidades principales a las que pertenecen los contratistas. Cada dependencia puede tener múltiples áreas.",
                    },
                },
                {
                    element: ".fi-ta-table",
                    popover: {
                        title: "Lista de Dependencias",
                        description:
                            "Aquí puedes ver todas las dependencias registradas con su nombre, código y estado.",
                        side: "top",
                    },
                },
                {
                    element: "[data-tour='create-button-dependencias']",
                    popover: {
                        title: "Crear Dependencia",
                        description:
                            "Añade nuevas dependencias según sea necesario para organizar mejor las afiliaciones.",
                        side: "bottom",
                    },
                },
                {
                    popover: {
                        title: "¡Listo!",
                        description:
                            "Las dependencias ayudan a organizar y filtrar las afiliaciones por entidad.",
                    },
                },
            ],
        });

        window.tourDependencias = tourDependencias;

        const tourDependenciasShown = localStorage.getItem(
            "tourDependenciasShown",
        );
        if (!tourDependenciasShown) {
            setTimeout(function () {
                tourDependencias.drive();
                localStorage.setItem("tourDependenciasShown", "true");
            }, 1000);
        }
    }

    // ========== TOUR PARA ÁREAS ==========
    if (pathname.endsWith("areas") || pathname.endsWith("areas/")) {
        const tourAreas = driverFn({
            showProgress: true,
            nextBtnText: "Siguiente",
            prevBtnText: "Anterior",
            doneBtnText: "Entendido",
            progressText: "Paso {{current}} de {{total}}",
            steps: [
                {
                    popover: {
                        title: "Gestión de Áreas",
                        description:
                            "Las áreas son subdivisiones dentro de cada dependencia. Permiten organizar mejor a los contratistas.",
                    },
                },
                {
                    element: ".fi-ta-table",
                    popover: {
                        title: "Lista de Áreas",
                        description:
                            "Cada área pertenece a una dependencia específica. Puedes ver el nombre, dependencia asociada y estado.",
                        side: "top",
                    },
                },
                {
                    element: "[data-tour='create-button-areas']",
                    popover: {
                        title: "Crear Área",
                        description:
                            "Añade nuevas áreas y asócialas a su dependencia correspondiente.",
                        side: "bottom",
                    },
                },
                {
                    popover: {
                        title: "¡Listo!",
                        description:
                            "Las áreas permiten una organización más detallada de los contratistas dentro de cada dependencia.",
                    },
                },
            ],
        });

        window.tourAreas = tourAreas;

        const tourAreasShown = localStorage.getItem("tourAreasShown");
        if (!tourAreasShown) {
            setTimeout(function () {
                tourAreas.drive();
                localStorage.setItem("tourAreasShown", "true");
            }, 1000);
        }
    }

    // ========== FUNCIONES GLOBALES ==========
    // Permitir reiniciar todos los tours
    window.reiniciarTourAfiliaciones = function () {
        localStorage.removeItem("tourAfiliacionesInicioShown");
        localStorage.removeItem("tourAfiliacionesListShown");
        localStorage.removeItem("tourAfiliacionesCreateShown");
        localStorage.removeItem("tourDependenciasShown");
        localStorage.removeItem("tourAreasShown");
        location.reload();
    };

    // Iniciar tour manualmente
    window.iniciarTour = function () {
        if (window.tourAfiliacionesList) {
            window.tourAfiliacionesList.drive();
        } else if (window.tourAfiliacionesCreate) {
            window.tourAfiliacionesCreate.drive();
        } else if (window.tourAfiliacionesInicio) {
            window.tourAfiliacionesInicio.drive();
        } else if (window.tourDependencias) {
            window.tourDependencias.drive();
        } else if (window.tourAreas) {
            window.tourAreas.drive();
        }
    };
});
