(function () {
    for (const gallery of document.querySelectorAll('.p20-gallery')) {
        const items = gallery.querySelector('.p20-gallery-items');
        const controls = gallery.querySelector('.p20-gallery-controls');
        if (controls) {
            const slides = Array.from(items.children);
            const pagination = gallery.querySelector('.p20-gallery-pagination');
            const dots = Array.from(pagination.querySelectorAll('[data-p20-index]'));
            const previous = controls.querySelector('[data-p20-step="-1"]');
            const next = controls.querySelector('[data-p20-step="1"]');
            const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
            const loop = controls.dataset.loop === '1';
            let active = 0;

            const offset = (slide) => {
                const track = items.getBoundingClientRect();
                const rect = slide.getBoundingClientRect();
                return getComputedStyle(items).direction === 'rtl'
                    ? rect.right - track.right
                    : rect.left - track.left;
            };
            const update = () => {
                dots.forEach((dot, index) => {
                    if (index === active) {
                        dot.setAttribute('aria-current', 'true');
                    } else {
                        dot.removeAttribute('aria-current');
                    }
                });
                previous.setAttribute('aria-disabled', String(!loop && active === 0));
                next.setAttribute('aria-disabled', String(!loop && active === slides.length - 1));
            };
            const goTo = (index) => {
                const target = loop ? (index + slides.length) % slides.length : index;
                if (slides[target]) {
                    items.scrollBy({
                        left: offset(slides[target]),
                        behavior: reducedMotion.matches || Math.abs(target - active) > 1 ? 'instant' : 'smooth',
                    });
                }
            };

            items.addEventListener('scroll', () => {
                let distance = Infinity;
                slides.forEach((slide, index) => {
                    const current = Math.abs(offset(slide));
                    if (current < distance) {
                        distance = current;
                        active = index;
                    }
                });
                update();
            }, { passive: true });
            previous.addEventListener('click', () => goTo(active - 1));
            next.addEventListener('click', () => goTo(active + 1));
            dots.forEach((dot, index) => dot.addEventListener('click', () => goTo(index)));
            if (controls.dataset.autoplay === '1') {
                const timer = setInterval(() => {
                    if (!gallery.isConnected) {
                        clearInterval(timer);
                    } else if (!reducedMotion.matches && !document.hidden &&
                        !pagination.matches(':hover, :focus-within') &&
                        !previous.matches(':hover') && !next.matches(':hover')) {
                        goTo(active + 1);
                    }
                }, 5000);
            }
            update();
            controls.hidden = false;
            pagination.hidden = false;
        }

        const dialog = gallery.querySelector('.p20-gallery-dialog');
        if (!dialog || typeof dialog.showModal !== 'function') {
            continue;
        }

        const image = dialog.querySelector('img');
        let opener;
        gallery.addEventListener('click', (event) => {
            const link = event.target.closest('.p20-gallery-full');
            if (!link || event.defaultPrevented || event.button !== 0 ||
                event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            event.preventDefault();
            opener = link;
            image.alt = link.querySelector('img').alt;
            image.src = link.href;
            dialog.showModal();
        });
        dialog.querySelector('button').addEventListener('click', () => dialog.close());
        let pressedBackdrop = false;
        dialog.addEventListener('pointerdown', (event) => {
            pressedBackdrop = event.target === dialog;
        });
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog && pressedBackdrop) {
                dialog.close();
            }
            pressedBackdrop = false;
        });
        dialog.addEventListener('close', () => {
            image.removeAttribute('src');
            if (opener) {
                opener.focus();
                opener = null;
            }
        });
    }
})();
