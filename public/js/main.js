document.addEventListener('DOMContentLoaded', () => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* Mobile navigation */
    const toggle = document.querySelector('[data-nav-toggle]');
    const menu = document.querySelector('[data-nav-menu]');

    if (toggle && menu) {
        toggle.addEventListener('click', () => {
            const open = menu.classList.toggle('open');
            toggle.setAttribute('aria-expanded', String(open));
        });

        menu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                menu.classList.remove('open');
                toggle.setAttribute('aria-expanded', 'false');
            });
        });
    }

    /* Subtle page entrance */
    requestAnimationFrame(() => document.body.classList.add('page-ready'));

    /* Scroll progress indicator */
    const progress = document.createElement('div');
    progress.className = 'scroll-progress';
    progress.setAttribute('aria-hidden', 'true');
    document.body.appendChild(progress);

    const updateScroll = () => {
        const scrollable = document.documentElement.scrollHeight - window.innerHeight;
        const amount = scrollable > 0 ? window.scrollY / scrollable : 0;
        progress.style.transform = `scaleX(${Math.min(1, Math.max(0, amount))})`;

        const header = document.querySelector('.site-header');
        if (header) header.classList.toggle('scrolled', window.scrollY > 12);
    };

    updateScroll();
    window.addEventListener('scroll', updateScroll, { passive: true });

    /* Reveal content as it enters the viewport */
    const revealTargets = [
        '.page-intro > *',
        '.home-section .section-heading > *',
        '.service-row',
        '.process-item',
        '.contact-item',
        '.detail-row',
        '.about-grid > *',
        '.value-row',
        '.price-row',
        '.review-intro > *',
        '.review-form',
        '.legal-section',
        '.service-cta > *'
    ];

    document.querySelectorAll(revealTargets.join(',')).forEach((el, index) => {
        if (!el.hasAttribute('data-reveal')) {
            el.setAttribute('data-reveal', index % 5 === 0 ? 'left' : 'up');
            el.style.transitionDelay = `${Math.min((index % 6) * 70, 350)}ms`;
        }
    });

    const revealObserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -45px 0px' });

    document.querySelectorAll('[data-reveal]').forEach(el => revealObserver.observe(el));

    /* Give pricing cards inserted later by pricing.js the same animation */
    const mutationObserver = new MutationObserver(mutations => {
        mutations.forEach(mutation => {
            mutation.addedNodes.forEach(node => {
                if (!(node instanceof HTMLElement)) return;
                const targets = node.matches('[data-reveal]') ? [node] : [...node.querySelectorAll('[data-reveal]')];
                targets.forEach(el => revealObserver.observe(el));
            });
        });
    });

    const pricingList = document.querySelector('[data-pricing]');
    if (pricingList) mutationObserver.observe(pricingList, { childList: true, subtree: true });

    /* Gentle parallax on the large background shapes */
    if (!prefersReducedMotion) {
        const hero = document.querySelector('.home-hero');
        const intro = document.querySelector('.page-intro');
        let ticking = false;

        const parallax = () => {
            const y = window.scrollY;
            if (hero && y < window.innerHeight * 1.4) {
                hero.style.setProperty('--parallax-y', `${y * 0.055}px`);
            }
            if (intro) {
                intro.style.setProperty('--parallax-y', `${y * 0.035}px`);
            }
            ticking = false;
        };

        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(parallax);
                ticking = true;
            }
        }, { passive: true });

        parallax();

        /* Floating detail orb on the hero */
        if (hero) {
            const orb = document.createElement('span');
            orb.className = 'floating-orb';
            orb.setAttribute('aria-hidden', 'true');
            orb.style.right = '18%';
            orb.style.top = '23%';
            orb.style.animationDelay = '-2s';
            hero.appendChild(orb);
        }
    }

    /* Slightly alive buttons — follows the pointer without being gimmicky */
    if (!prefersReducedMotion) {
        document.querySelectorAll('.cut-button').forEach(button => {
            button.addEventListener('pointermove', event => {
                const rect = button.getBoundingClientRect();
                const x = (event.clientX - rect.left) / rect.width - 0.5;
                const y = (event.clientY - rect.top) / rect.height - 0.5;
                button.style.transform = `translate(${x * 5}px, ${y * 4}px) translateY(-2px)`;
            });

            button.addEventListener('pointerleave', () => {
                button.style.transform = '';
            });
        });
    }

    /* Smooth transitions between internal pages */
    document.querySelectorAll('a[href]').forEach(link => {
        const url = new URL(link.href, window.location.href);
        const sameSite = url.origin === window.location.origin;
        const internalPage = sameSite && url.pathname.endsWith('.html');
        const modifiedClick = event => event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || link.target === '_blank';

        if (internalPage) {
            link.addEventListener('click', event => {
                if (modifiedClick(event) || prefersReducedMotion) return;
                event.preventDefault();
                document.body.classList.add('page-leaving');
                setTimeout(() => { window.location.href = link.href; }, 220);
            });
        }
    });

    /* Public review form */
    const reviewForm = document.querySelector('[data-review-form]');
    if (reviewForm) {
        const status = reviewForm.querySelector('[data-form-status]');

        reviewForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const button = reviewForm.querySelector('button[type="submit"]');

            if (!reviewForm.reportValidity()) return;

            button.disabled = true;
            status.textContent = 'Sending your review...';

            try {
                const response = await fetch(reviewForm.action, {
                    method: 'POST',
                    body: new FormData(reviewForm),
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) throw new Error('Submission failed');

                reviewForm.reset();
                status.textContent = 'Thank you. Your review has been sent.';
            } catch (error) {
                status.textContent = 'We could not send the review right now. Please use WhatsApp or email instead.';
            } finally {
                button.disabled = false;
            }
        });
    }
});
