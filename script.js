let animationObserver;
let premiumSystem = null;

const projectsData = {
    0: {
        title: '🛒 Plataforma E-commerce',
        description: 'Sistema completo de vendas online com React.js e Node.js - 15.000+ usuários ativos',
        image: 'https://via.placeholder.com/400x300/667eea/ffffff?text=E-commerce+Platform',
        tech: ['React', 'Node.js', 'MongoDB', 'Stripe'],
        features: ['Sistema de pagamento integrado', 'Painel administrativo completo', 'Carrinho de compras avançado', 'Sistema de avaliações', 'Notificações em tempo real', 'Relatórios de vendas'],
        stats: [{ number: '15.000+', label: 'Usuários Ativos' }, { number: '98.5%', label: 'Uptime' }, { number: '4.8★', label: 'Avaliação' }, { number: '2.5M+', label: 'Transações' }],
        demo: 'https://ecommerce-demo.com',
        code: 'https://github.com/carlosdev/ecommerce'
    },
    1: {
        title: '🤖 Dashboard Analytics IA',
        description: 'Painel inteligente de análise com Python e TensorFlow - 98.7% de precisão',
        image: 'https://via.placeholder.com/400x300/4ecdc4/ffffff?text=AI+Dashboard',
        tech: ['Python', 'TensorFlow', 'React', 'D3.js'],
        features: ['Análise preditiva avançada', 'Visualizações interativas', 'Machine Learning integrado', 'Processamento em tempo real', 'Relatórios automatizados', 'API REST completa'],
        stats: [{ number: '98.7%', label: 'Precisão IA' }, { number: '500GB+', label: 'Dados Processados' }, { number: '24/7', label: 'Monitoramento' }, { number: '50+', label: 'Modelos ML' }],
        demo: 'https://ai-dashboard-demo.com',
        code: 'https://github.com/carlosdev/ai-dashboard'
    },
    2: {
        title: '💻 Aplicação Desktop',
        description: 'Software desktop multiplataforma desenvolvido com Electron - 50.000+ downloads',
        image: 'https://via.placeholder.com/400x300/45b7d1/ffffff?text=Desktop+App',
        tech: ['Electron', 'Vue.js', 'SQLite', 'Node.js'],
        features: ['Interface nativa multiplataforma', 'Sincronização na nuvem', 'Modo offline completo', 'Atualizações automáticas', 'Backup automático', 'Suporte a plugins'],
        stats: [{ number: '50.000+', label: 'Downloads' }, { number: '4.9★', label: 'Avaliação' }, { number: '3', label: 'Plataformas' }, { number: '99.2%', label: 'Estabilidade' }],
        demo: 'https://desktop-app-demo.com',
        code: 'https://github.com/carlosdev/desktop-app'
    }
};

document.addEventListener('DOMContentLoaded', () => {
    initializeApp();
});

function initializeApp() {
    initializeNavigation();
    initializeAnimations();
    initializeCounters();
    initializeSkillBars();
    initializeScrollEffects();
    initializeMobileMenu();
    initializeSmoothScrolling();
    initializeHeaderScroll();
    initTestimonialCarousel();
    initCredibilityAnimations();
    initEnhanced3DBackground();
    initProjectModals();
    initContactForm();
    initButtonEffects();
    initializeInteractiveEffects();
    initializeAccessibility();
    initProjectPreviews();
    initPriceCounters();
    initSectionMotion();
    initSkeletons();

    premiumSystem = new PremiumInteractionSystem();
}

function initializeNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    window.addEventListener('scroll', updateActiveNavLink, { passive: true });

    navLinks.forEach((link) => {
        link.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (!href || !href.startsWith('#')) return;
            e.preventDefault();
            scrollToTarget(href);
        });
    });
}

function updateActiveNavLink() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');
    const header = document.querySelector('.header');
    const headerHeight = header ? header.offsetHeight : 0;
    let currentSection = '';

    sections.forEach((section) => {
        const sectionTop = section.offsetTop - headerHeight - 100;
        const sectionHeight = section.offsetHeight;
        if (window.scrollY >= sectionTop && window.scrollY < sectionTop + sectionHeight) {
            currentSection = section.getAttribute('id');
        }
    });

    navLinks.forEach((link) => {
        link.classList.toggle('active', link.getAttribute('href') === `#${currentSection}`);
    });
}

function initializeMobileMenu() {
    const hamburger = document.getElementById('hamburger');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileLinks = document.querySelectorAll('.mobile-link');

    if (hamburger) {
        hamburger.addEventListener('click', toggleMobileMenu);
    }

    mobileLinks.forEach((link) => link.addEventListener('click', closeMobileMenu));

    if (hamburger && mobileMenu) {
        document.addEventListener('click', (e) => {
            if (!hamburger.contains(e.target) && !mobileMenu.contains(e.target)) {
                closeMobileMenu();
            }
        });
    }
}

function toggleMobileMenu() {
    const hamburger = document.getElementById('hamburger');
    const mobileMenu = document.getElementById('mobileMenu');
    if (!hamburger || !mobileMenu) return;

    hamburger.classList.toggle('active');
    mobileMenu.classList.toggle('active');
    const isOpen = mobileMenu.classList.contains('active');
    hamburger.setAttribute('aria-expanded', String(isOpen));
    document.body.style.overflow = isOpen ? 'hidden' : 'auto';
}

function closeMobileMenu() {
    const hamburger = document.getElementById('hamburger');
    const mobileMenu = document.getElementById('mobileMenu');
    if (hamburger) {
        hamburger.classList.remove('active');
        hamburger.setAttribute('aria-expanded', 'false');
    }
    if (mobileMenu) mobileMenu.classList.remove('active');
    document.body.style.overflow = 'auto';
}

function initializeAnimations() {
    const animatedElements = document.querySelectorAll('.fade-in, .service-card, .feature-card, .success-card, .story-card, .roi-card, .package-card, .stat-card, .credibility-item, .tech-item');

    animationObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, {
        threshold: 0.12,
        rootMargin: '0px 0px -40px 0px'
    });

    animatedElements.forEach((element) => {
        element.classList.add('fade-in');
        animationObserver.observe(element);
    });
}

function initializeCounters() {
    const counters = document.querySelectorAll('.stat-number[data-target], .credibility-number[data-target], .roi-number[data-target]');
    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            animateCounter(entry.target);
            obs.unobserve(entry.target);
        });
    }, { threshold: 0.35 });

    counters.forEach((counter) => observer.observe(counter));
}

function animateCounter(element) {
    const target = Number(element.dataset.target);
    const decimals = Number(element.dataset.decimals || 0);
    const prefix = element.dataset.prefix || '';
    const suffix = element.dataset.suffix || '';
    const duration = 1800;
    const startTime = performance.now();

    function tick(now) {
        const progress = Math.min((now - startTime) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = target * eased;
        const value = decimals > 0 ? current.toFixed(decimals) : Math.floor(current).toLocaleString('pt-BR');
        element.textContent = `${prefix}${value}${suffix}`;

        if (progress < 1) {
            requestAnimationFrame(tick);
        } else {
            const finalValue = decimals > 0 ? target.toFixed(decimals) : target.toLocaleString('pt-BR');
            element.textContent = `${prefix}${finalValue}${suffix}`;
        }
    }

    requestAnimationFrame(tick);
}

function initializeSkillBars() {
    const skillContainer = document.querySelector('.skill-bars');
    if (!skillContainer) return;

    const skillObserver = new IntersectionObserver((entries, obs) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            document.querySelectorAll('.skill-fill').forEach((bar, index) => {
                setTimeout(() => {
                    bar.style.width = `${bar.dataset.width}%`;
                }, index * 150);
            });
            obs.unobserve(entry.target);
        });
    }, { threshold: 0.45 });

    skillObserver.observe(skillContainer);
}

function initializeScrollEffects() {
    const timelineItems = document.querySelectorAll('.timeline-item');

    timelineItems.forEach((item, index) => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    setTimeout(() => entry.target.classList.add('animate'), index * 160);
                }
            });
        }, { threshold: 0.25 });

        observer.observe(item);
    });
}

function handleParallax() {
    const scrolled = window.pageYOffset;
    document.querySelectorAll('.shape').forEach((shape, index) => {
        const driftY = -(scrolled * (0.08 + index * 0.01));
        const rotate = scrolled * 0.02;
        shape.style.setProperty('--scroll-transform', `translate3d(0, ${driftY}px, 0) rotate(${rotate}deg)`);
    });
}

function initializeSmoothScrolling() {
    document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (!href || href === '#') return;
            if (href.startsWith('#')) {
                e.preventDefault();
                scrollToTarget(href);
            }
        });
    });
}

function scrollToTarget(selector) {
    const target = document.querySelector(selector);
    const header = document.querySelector('.header');
    if (!target) return;

    const headerHeight = header ? header.offsetHeight : 0;
    const targetPosition = target.offsetTop - headerHeight;
    window.scrollTo({ top: targetPosition, behavior: 'smooth' });
}

function initializeHeaderScroll() {
    const header = document.querySelector('.header');
    if (!header) return;
    let lastScrollY = window.scrollY;

    window.addEventListener('scroll', () => {
        const currentScrollY = window.scrollY;
        header.style.background = currentScrollY > 100 ? 'rgba(10, 10, 15, 0.95)' : 'rgba(10, 10, 15, 0.9)';
        header.style.backdropFilter = 'blur(20px)';
        header.style.boxShadow = currentScrollY > 100 ? '0 2px 20px rgba(0, 0, 0, 0.1)' : 'none';
        header.style.transform = currentScrollY > lastScrollY && currentScrollY > 200 ? 'translateY(-100%)' : 'translateY(0)';
        lastScrollY = currentScrollY;
    }, { passive: true });
}

function initTestimonialCarousel() {
    const testimonials = document.querySelectorAll('.testimonial-item');
    const dots = document.querySelectorAll('.dot');
    let currentSlide = 0;
    if (!testimonials.length) return;

    function showSlide(index) {
        testimonials.forEach((testimonial, i) => testimonial.classList.toggle('active', i === index));
        dots.forEach((dot, i) => dot.classList.toggle('active', i === index));
    }

    setInterval(() => {
        currentSlide = (currentSlide + 1) % testimonials.length;
        showSlide(currentSlide);
    }, 4000);

    dots.forEach((dot, index) => dot.addEventListener('click', () => {
        currentSlide = index;
        showSlide(currentSlide);
    }));
}

function initCredibilityAnimations() {
    const techShowcase = document.querySelector('.tech-showcase');
    if (!techShowcase) return;

    const techObserver = new IntersectionObserver((entries, obs) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.querySelectorAll('.tech-fill').forEach((fill) => {
                const width = fill.style.width;
                fill.style.width = '0%';
                requestAnimationFrame(() => {
                    setTimeout(() => { fill.style.width = width; }, 150);
                });
            });
            obs.unobserve(entry.target);
        });
    }, { threshold: 0.35 });

    techObserver.observe(techShowcase);
}

function initEnhanced3DBackground() {
    const animatedBg = document.querySelector('.animated-background');
    const floatingShapes = document.querySelector('.floating-shapes');
    if (!animatedBg || !floatingShapes) return;

    for (let i = 9; i <= 15; i += 1) {
        const shape = document.createElement('div');
        shape.className = `shape shape-${i}`;
        const variants = ['', 'cube', 'triangle'];
        const type = variants[Math.floor(Math.random() * variants.length)];
        if (type) shape.classList.add(type);

        const size = Math.random() * 80 + 40;
        shape.style.width = `${size}px`;
        shape.style.height = `${size}px`;
        shape.style.top = `${Math.random() * 100}%`;
        shape.style.left = `${Math.random() * 100}%`;
        shape.style.animationDelay = `${Math.random() * -24}s`;
        floatingShapes.appendChild(shape);
    }

    for (let i = 0; i < 18; i += 1) {
        const particle = document.createElement('div');
        particle.className = 'bg-particles';
        particle.style.left = `${Math.random() * 100}%`;
        particle.style.animationDelay = `${Math.random() * -15}s`;
        particle.style.animationDuration = `${Math.random() * 8 + 10}s`;
        animatedBg.appendChild(particle);
    }

    const updatePointer = (x, y) => {
        const mouseX = x / window.innerWidth - 0.5;
        const mouseY = y / window.innerHeight - 0.5;
        animatedBg.querySelectorAll('.shape').forEach((shape, index) => {
            const factor = (index % 3 + 1) * 6;
            shape.style.setProperty('--pointer-transform', `translate3d(${mouseX * factor}px, ${mouseY * factor}px, 0)`);
        });
    };

    document.addEventListener('mousemove', (e) => updatePointer(e.clientX, e.clientY), { passive: true });
    document.addEventListener('touchmove', (e) => {
        const touch = e.touches[0];
        if (touch) updatePointer(touch.clientX, touch.clientY);
    }, { passive: true });
}

function initProjectModals() {
    const modal = document.getElementById('projectModal');
    const closeBtn = document.querySelector('.close');
    const portfolioBtns = document.querySelectorAll('.portfolio-btn');
    if (!modal || !closeBtn) return;

    portfolioBtns.forEach((btn, index) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openModal(projectsData[index]);
        });
    });

    closeBtn.addEventListener('click', closeModal);

    window.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'block') closeModal();
    });

    function openModal(project) {
        if (!project) return;
        document.getElementById('modalTitle').textContent = project.title;
        document.getElementById('modalDescription').textContent = project.description;
        document.getElementById('modalImage').src = project.image;
        document.getElementById('modalDemo').href = project.demo;
        document.getElementById('modalCode').href = project.code;

        const techContainer = document.getElementById('modalTech');
        const featuresContainer = document.getElementById('modalFeatures');
        const statsContainer = document.getElementById('modalStats');

        techContainer.innerHTML = project.tech.map((tech) => `<span>${tech}</span>`).join('');
        featuresContainer.innerHTML = project.features.map((feature) => `<li>${feature}</li>`).join('');
        statsContainer.innerHTML = project.stats.map((stat) => `
            <div class="stat-item">
                <span class="stat-number">${stat.number}</span>
                <span class="stat-label">${stat.label}</span>
            </div>
        `).join('');

        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function initContactForm() {
    const form = document.querySelector('.contact-form');
    const submitBtn = document.querySelector('.submit-btn');
    if (!form || !submitBtn) return;

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const name = form.querySelector('input[type="text"]')?.value.trim();
        const email = form.querySelector('input[type="email"]')?.value.trim();
        const message = form.querySelector('textarea')?.value.trim();

        if (!name || !email || !message) {
            showNotification('Por favor, preencha todos os campos!', 'error');
            return;
        }

        if (!isValidEmail(email)) {
            showNotification('Por favor, insira um email válido!', 'error');
            return;
        }

        submitBtn.textContent = 'Enviando...';
        submitBtn.disabled = true;

        setTimeout(() => {
            showNotification('Mensagem enviada com sucesso! Retornarei em breve.', 'success');
            form.reset();
            submitBtn.textContent = 'Enviar Mensagem';
            submitBtn.disabled = false;
        }, 1500);
    });
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showNotification(message, type = 'info') {
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) existingNotification.remove();

    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-icon">${type === 'success' ? '✓' : type === 'error' ? '✗' : 'ℹ'}</span>
            <span class="notification-message">${message}</span>
            <button class="notification-close">×</button>
        </div>
    `;

    document.body.appendChild(notification);
    requestAnimationFrame(() => notification.classList.add('show'));
    notification.querySelector('.notification-close').addEventListener('click', () => closeNotification(notification));
    setTimeout(() => closeNotification(notification), 5000);
}

function closeNotification(notification) {
    if (!notification) return;
    notification.classList.remove('show');
    setTimeout(() => notification.remove(), 250);
}

function initButtonEffects() {
    const buttons = document.querySelectorAll('button, .btn, .portfolio-btn, .premium-btn');
    buttons.forEach((button) => {
        button.addEventListener('mousedown', function (e) {
            createRippleEffect(this, e);
        });
    });
}

function createRippleEffect(button, event) {
    const ripple = document.createElement('span');
    const rect = button.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;

    ripple.style.width = ripple.style.height = `${size}px`;
    ripple.style.left = `${x}px`;
    ripple.style.top = `${y}px`;
    ripple.classList.add('ripple');
    button.appendChild(ripple);
    setTimeout(() => ripple.remove(), 600);
}

function initializeInteractiveEffects() {
    const cards = document.querySelectorAll('.service-card, .timeline-content, .success-card, .story-card, .roi-card, .stat-card, .package-card');
    cards.forEach((card) => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width - 0.5) * 10;
            const y = ((e.clientY - rect.top) / rect.height - 0.5) * -10;
            card.style.setProperty('--tilt-transform', `rotateX(${y}deg) rotateY(${x}deg) translateY(-8px)`);
        });
        card.addEventListener('mouseleave', () => {
            card.style.setProperty('--tilt-transform', 'rotateX(0deg) rotateY(0deg) translateY(0)');
        });
    });
}

function initializeAccessibility() {
    const mobileMenu = document.getElementById('mobileMenu');
    if (!mobileMenu) return;
    const focusableElements = mobileMenu.querySelectorAll('a, button');
    if (!focusableElements.length) return;

    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeMobileMenu();
        if (e.key === 'Tab') document.body.classList.add('keyboard-navigation');
    });

    document.addEventListener('mousedown', () => document.body.classList.remove('keyboard-navigation'));

    mobileMenu.addEventListener('keydown', (e) => {
        if (e.key !== 'Tab') return;
        if (e.shiftKey && document.activeElement === firstElement) {
            e.preventDefault();
            lastElement.focus();
        } else if (!e.shiftKey && document.activeElement === lastElement) {
            e.preventDefault();
            firstElement.focus();
        }
    });
}

function initProjectPreviews() {
    const navItems = document.querySelectorAll('.nav-item');
    const previewBody = document.getElementById('previewBody');
    if (!navItems.length || !previewBody) return;

    const views = {
        home: '<div class="preview-simulation preview-transition"><div class="sim-hero"></div><div class="sim-grid"><div class="sim-item"></div><div class="sim-item"></div><div class="sim-item"></div></div></div>',
        produtos: '<div class="preview-simulation preview-transition"><div class="sim-grid preview-products"><div class="sim-item" style="height: 120px"></div><div class="sim-item" style="height: 120px"></div><div class="sim-item" style="height: 120px"></div><div class="sim-item" style="height: 120px"></div></div></div>',
        carrinho: '<div class="preview-simulation preview-transition"><div class="sim-item" style="height: 60px; margin-bottom: 10px;"></div><div class="sim-item" style="height: 60px; margin-bottom: 10px;"></div><div class="sim-item sim-cart-highlight" style="height: 100px; margin-top: 20px; background: rgba(99, 102, 241, 0.2);"></div></div>'
    };

    navItems.forEach((item) => {
        item.addEventListener('click', () => {
            navItems.forEach((nav) => nav.classList.remove('active'));
            item.classList.add('active');
            const key = item.dataset.previewView || 'home';
            previewBody.style.opacity = '0';
            previewBody.style.transform = 'translateY(8px)';
            setTimeout(() => {
                previewBody.innerHTML = views[key] || views.home;
                previewBody.style.opacity = '1';
                previewBody.style.transform = 'translateY(0)';
            }, 180);
        });
    });
}

function initPriceCounters() {
    const prices = document.querySelectorAll('.price-counter[data-price]');
    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            animatePrice(entry.target);
            obs.unobserve(entry.target);
        });
    }, { threshold: 0.45 });

    prices.forEach((price) => observer.observe(price));
}

function animatePrice(element) {
    const target = Number(element.dataset.price);
    const startsWith = element.textContent.toLowerCase().includes('a partir');
    const prefix = startsWith ? 'A partir de R$' : 'R$ ';
    const duration = 1200;
    const start = performance.now();

    function frame(now) {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const value = Math.floor(target * eased).toLocaleString('pt-BR');
        element.textContent = `${prefix}${value}`;
        if (progress < 1) requestAnimationFrame(frame);
    }

    requestAnimationFrame(frame);
}

function initSectionMotion() {
    const section = document.querySelector('.interactive-metrics');
    if (!section) return;

    const applyMotion = (clientX, clientY) => {
        const rect = section.getBoundingClientRect();
        const x = ((clientX - rect.left) / rect.width - 0.5) * 18;
        const y = ((clientY - rect.top) / rect.height - 0.5) * 18;
        section.style.setProperty('--metrics-x', `${x}px`);
        section.style.setProperty('--metrics-y', `${y}px`);
    };

    section.addEventListener('mousemove', (e) => applyMotion(e.clientX, e.clientY));
    section.addEventListener('touchmove', (e) => {
        const touch = e.touches[0];
        if (touch) applyMotion(touch.clientX, touch.clientY);
    }, { passive: true });
    section.addEventListener('mouseleave', () => {
        section.style.setProperty('--metrics-x', '0px');
        section.style.setProperty('--metrics-y', '0px');
    });
}

function initSkeletons() {
    const targets = document.querySelectorAll('.skeleton-target');
    window.addEventListener('load', () => {
        setTimeout(() => {
            targets.forEach((item) => item.classList.add('loaded'));
        }, 280);
    });
}

class PremiumInteractionSystem {
    constructor() {
        this.setupWhatsAppLinks();
        this.setupFloatingWhatsApp();
    }

    setupWhatsAppLinks() {
        const whatsappNumber = '5543996593590';
        const packages = {
            'package-personalizado': { name: 'Personalizado', price: 'A partir de R$250', features: 'Design UI/UX Exclusivo, Sistema personalizado, Integração de APIs, Pequenas alterações, Documentação Completa' },
            'package-enterprise': { name: 'Enterprise Pro', price: 'A partir de R$800', features: 'Tudo do Personalizado, Integração ao Back-End, Banco de Dados, Deploy em Produção, Suporte 30 dias' },
            'package-ai': { name: 'AI Revolution', price: 'A partir de R$1.500', features: 'Tudo do Enterprise Pro, Machine Learning, Automação Python, IA Personalizada, Analytics Avançado' },
            'package-transformacao': { name: 'Transformação Digital', price: 'R$ 2.400', features: 'Consultoria Estratégica, Arquitetura Completa, Migração de Sistemas, Treinamento Equipe, Suporte 6 meses' },
            'package-completo': { name: 'Plano Completo', price: 'A combinar', features: 'Landing Pages, A/B Testing, Analytics Setup, Otimização SEO, Relatórios Mensais' },
            'package-corporacao': { name: 'Corporação', price: 'A combinar', features: 'Solução Enterprise, Multi-plataforma, Segurança Avançada, Escalabilidade, Suporte 24/7' }
        };

        Object.entries(packages).forEach(([packageId, pkg]) => {
            const button = document.getElementById(packageId);
            if (!button) return;
            button.addEventListener('click', () => {
                const message = `Olá Carlos! 👋\n\nTenho interesse no plano *${pkg.name}* (${pkg.price})\n\n📋 *Recursos inclusos:*\n${pkg.features}\n\nGostaria de saber mais detalhes e como podemos começar o projeto!\n\nObrigado! 🚀`;
                window.open(`https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`, '_blank');
            });
        });
    }

    setupFloatingWhatsApp() {
        const floatingBtn = document.getElementById('floating-whatsapp');
        if (!floatingBtn) return;
        floatingBtn.addEventListener('click', () => {
            const message = encodeURIComponent('Olá! Gostaria de saber mais sobre seus serviços de desenvolvimento. Vim através do seu site.');
            window.open(`https://wa.me/5543996593590?text=${message}`, '_blank');
        });

        setTimeout(() => {
            floatingBtn.style.opacity = '1';
            floatingBtn.style.transform = 'scale(1)';
        }, 800);
    }
}

function debounce(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

function throttle(func, limit) {
    let inThrottle = false;
    return function (...args) {
        if (inThrottle) return;
        func.apply(this, args);
        inThrottle = true;
        setTimeout(() => { inThrottle = false; }, limit);
    };
}

const optimizedScroll = throttle(() => {
    updateActiveNavLink();
    handleParallax();
}, 16);
window.addEventListener('scroll', optimizedScroll, { passive: true });

window.addEventListener('resize', debounce(() => {
    if (window.innerWidth > 768) closeMobileMenu();
}, 220));

window.addEventListener('error', (e) => {
    console.error('Erro no JavaScript:', e.error);
});

if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        toggleMobileMenu,
        animateCounter,
        initializeSkillBars
    };
}
