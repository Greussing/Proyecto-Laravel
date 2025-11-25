/**
 * Dropdown Overflow Fix (Corregido)
 * Clona dropdowns SOLO cuando están recortados o fuera del viewport.
 * Evita el doble dropdown en el PRIMER filtro.
 */

document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => initDropdownFix(), 500);
});

function initDropdownFix() {
    console.log('🔧 Dropdown Fix: Inicializando...');

    const dropdownContainers = document.querySelectorAll('[x-data]');
    let fixedCount = 0;

    dropdownContainers.forEach(container => {

        // ❌ Excluir marcados
        if (container.hasAttribute('data-no-fix') || container.closest('[data-no-fix]')) return;

        // ❌ Excluir navbar / header / sidebar
        if (container.closest('nav') || container.closest('header') || container.closest('aside')) return;

        const dropdown = container.querySelector('[x-show]');
        if (!dropdown) return;

        // Evitar doble procesamiento
        if (dropdown.dataset.dropdownFixed === 'true') return;
        dropdown.dataset.dropdownFixed = 'true';

        const trigger = container.querySelector('button, [role="button"]');
        if (!trigger) return;

        fixedCount++;
        console.log(`✅ Procesando dropdown #${fixedCount}`);

        let clonedDropdown = null;

        // -------------------------------
        // Detectar si el dropdown está recortado
        // -------------------------------
        function isClipped(el) {
            const rect = el.getBoundingClientRect();
            const vpWidth = window.innerWidth || document.documentElement.clientWidth;
            const vpHeight = window.innerHeight || document.documentElement.clientHeight;

            // Si sale del viewport → necesita clone
            if (rect.top < 0 || rect.bottom > vpHeight ||
                rect.left < 0 || rect.right > vpWidth) {
                return true;
            }

            // Si un contenedor con overflow lo corta
            let node = el.parentElement;
            while (node && node !== document.body) {
                const style = getComputedStyle(node);
                if (/(hidden|auto|scroll)/.test(style.overflow + style.overflowX + style.overflowY)) {
                    const parentRect = node.getBoundingClientRect();
                    if (
                        rect.bottom > parentRect.bottom ||
                        rect.top < parentRect.top ||
                        rect.left < parentRect.left ||
                        rect.right > parentRect.right
                    ) {
                        return true;
                    }
                }
                node = node.parentElement;
            }
            return false;
        }

        // -------------------------------
        // Posicionar clon
        // -------------------------------
        function positionClone() {
            if (!clonedDropdown) return;
            const rect = trigger.getBoundingClientRect();
            clonedDropdown.style.top = `${rect.bottom + window.scrollY}px`;
            clonedDropdown.style.left = `${rect.left + window.scrollX}px`;
            clonedDropdown.style.minWidth = `${rect.width}px`;
        }

        // -------------------------------
        // Eliminar clon
        // -------------------------------
        function cleanupClone() {
            if (!clonedDropdown) return;
            clonedDropdown.remove();
            clonedDropdown = null;
            dropdown.style.visibility = '';
            dropdown.style.pointerEvents = '';
        }

        // -------------------------------
        // Crear clon
        // -------------------------------
        function createClone() {
            if (clonedDropdown) return;

            clonedDropdown = dropdown.cloneNode(true);
            clonedDropdown.style.position = 'absolute';
            clonedDropdown.style.visibility = 'visible';
            clonedDropdown.style.pointerEvents = 'auto';
            clonedDropdown.style.zIndex = 999999;

            document.body.appendChild(clonedDropdown);
            positionClone();
        }

        // -------------------------------
        // Observer corregido con delay REAL
        // -------------------------------
        let timer = null;

        const observer = new MutationObserver(() => {
            if (timer) clearTimeout(timer);

            // Esperar para que Alpine termine su animación (evita el doble del primer dropdown)
            timer = setTimeout(() => {

                const visible = getComputedStyle(dropdown).display !== 'none' &&
                                !dropdown.hasAttribute('hidden');

                if (!visible) {
                    cleanupClone();
                    return;
                }

                // Aquí Alpine ya abrió el dropdown → evaluar si está recortado
                if (isClipped(dropdown)) {
                    dropdown.style.visibility = 'hidden';
                    dropdown.style.pointerEvents = 'none';
                    createClone();
                } else {
                    // Está normal → no clonar
                    cleanupClone();
                }

            }, 25); // Delay seguro para Alpine (25ms)

        });

        observer.observe(dropdown, {
            attributes: true,
            attributeFilter: ['style', 'hidden', 'class']
        });

    });

    console.log(`🎉 Dropdown Fix completado: ${fixedCount} dropdowns procesados`);
}