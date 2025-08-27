// ===== GLOBAL VARIABLES =====
let animationObserver;

// ===== DOM CONTENT LOADED =====
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

// ===== INITIALIZE APPLICATION =====
function initializeApp() {

    
    // Initialize navigation
    initializeNavigation();
    
    // Initialize animations
    initializeAnimations();
    
    // Initialize counters
    initializeCounters();
    
    // Initialize skill bars
    initializeSkillBars();
    
    // Initialize scroll effects
    initializeScrollEffects();
    
    // Initialize mobile menu
    initializeMobileMenu();
    
    // Initialize smooth scrolling
    initializeSmoothScrolling();
    
    // Initialize header scroll effect
    initializeHeaderScroll();
    
    // Initialize testimonial carousel
    initTestimonialCarousel();
    
    // Initialize credibility animations
    initCredibilityAnimations();
    
    // Initialize enhanced 3D background
    initEnhanced3DBackground();
    
    // Initialize project modals
    initProjectModals();
    
    // Initialize contact form
    initContactForm();
    
    // Initialize button effects
    initButtonEffects();
}

// Sistema de Modais dos Projetos
function initProjectModals() {
    const modal = document.getElementById('projectModal');
    const closeBtn = document.querySelector('.close');
    const portfolioBtns = document.querySelectorAll('.portfolio-btn');
    
    // Dados dos projetos
    const projectsData = {
        0: {
            title: '🛒 Plataforma E-commerce',
            description: 'Sistema completo de vendas online com React.js e Node.js - 15.000+ usuários ativos',
            image: 'https://via.placeholder.com/400x300/667eea/ffffff?text=E-commerce+Platform',
            tech: ['React', 'Node.js', 'MongoDB', 'Stripe'],
            features: [
                'Sistema de pagamento integrado',
                'Painel administrativo completo',
                'Carrinho de compras avançado',
                'Sistema de avaliações',
                'Notificações em tempo real',
                'Relatórios de vendas'
            ],
            stats: [
                { number: '15.000+', label: 'Usuários Ativos' },
                { number: '98.5%', label: 'Uptime' },
                { number: '4.8★', label: 'Avaliação' },
                { number: '2.5M+', label: 'Transações' }
            ],
            demo: 'https://ecommerce-demo.com',
            code: 'https://github.com/carlosdev/ecommerce'
        },
        1: {
            title: '🤖 Dashboard Analytics IA',
            description: 'Painel inteligente de análise com Python e TensorFlow - 98.7% de precisão',
            image: 'https://via.placeholder.com/400x300/4ecdc4/ffffff?text=AI+Dashboard',
            tech: ['Python', 'TensorFlow', 'React', 'D3.js'],
            features: [
                'Análise preditiva avançada',
                'Visualizações interativas',
                'Machine Learning integrado',
                'Processamento em tempo real',
                'Relatórios automatizados',
                'API REST completa'
            ],
            stats: [
                { number: '98.7%', label: 'Precisão IA' },
                { number: '500GB+', label: 'Dados Processados' },
                { number: '24/7', label: 'Monitoramento' },
                { number: '50+', label: 'Modelos ML' }
            ],
            demo: 'https://ai-dashboard-demo.com',
            code: 'https://github.com/carlosdev/ai-dashboard'
        },
        2: {
            title: '💻 Aplicação Desktop',
            description: 'Software desktop multiplataforma desenvolvido com Electron - 50.000+ downloads',
            image: 'https://via.placeholder.com/400x300/45b7d1/ffffff?text=Desktop+App',
            tech: ['Electron', 'Vue.js', 'SQLite', 'Node.js'],
            features: [
                'Interface nativa multiplataforma',
                'Sincronização na nuvem',
                'Modo offline completo',
                'Atualizações automáticas',
                'Backup automático',
                'Suporte a plugins'
            ],
            stats: [
                { number: '50.000+', label: 'Downloads' },
                { number: '4.9★', label: 'Avaliação' },
                { number: '3', label: 'Plataformas' },
                { number: '99.2%', label: 'Estabilidade' }
            ],
            demo: 'https://desktop-app-demo.com',
            code: 'https://github.com/carlosdev/desktop-app'
        },
        3: {
            title: '🧠 Sistema de Inteligência Artificial',
            description: 'Automação empresarial inteligente com Machine Learning - 85% redução de tempo',
            image: 'https://via.placeholder.com/400x300/96ceb4/ffffff?text=AI+System',
            tech: ['Python', 'TensorFlow', 'Docker', 'Kubernetes'],
            features: [
                'Processamento de linguagem natural',
                'Automação de processos',
                'Análise de sentimentos',
                'Reconhecimento de padrões',
                'API de integração',
                'Dashboard de monitoramento'
            ],
            stats: [
                { number: '85%', label: 'Redução de Tempo' },
                { number: '99.1%', label: 'Precisão' },
                { number: '1M+', label: 'Documentos Processados' },
                { number: '24/7', label: 'Operação' }
            ],
            demo: 'https://ai-system-demo.com',
            code: 'https://github.com/carlosdev/ai-system'
        },
        4: {
            title: '📱 Aplicativo Mobile Híbrido',
            description: 'App multiplataforma com React Native e funcionalidades nativas - 4.8★ na Play Store',
            image: 'https://via.placeholder.com/400x300/ffeaa7/333333?text=Mobile+App',
            tech: ['React Native', 'Flutter', 'Ionic', 'Firebase'],
            features: [
                'Interface nativa em ambas plataformas',
                'Push notifications',
                'Geolocalização avançada',
                'Câmera e galeria integradas',
                'Pagamentos in-app',
                'Sincronização offline'
            ],
            stats: [
                { number: '4.8★', label: 'Play Store' },
                { number: '100K+', label: 'Downloads' },
                { number: '2', label: 'Plataformas' },
                { number: '95%', label: 'Retenção' }
            ],
            demo: 'https://mobile-app-demo.com',
            code: 'https://github.com/carlosdev/mobile-app'
        },
        5: {
            title: '🏆 Sistema Corporativo Premium',
            description: 'Plataforma empresarial de alta performance com IA integrada - 200+ empresas Fortune 500',
            image: 'https://via.placeholder.com/400x300/dda0dd/ffffff?text=Premium+System',
            tech: ['React.js', 'Node.js', 'MySQL', 'AI/ML', 'AWS'],
            features: [
                'IA integrada para automação',
                'Segurança enterprise avançada',
                'Escalabilidade automática',
                'Integração com ERPs',
                'Relatórios em tempo real',
                'Suporte 24/7 premium'
            ],
            stats: [
                { number: '200+', label: 'Empresas Fortune 500' },
                { number: '99.9%', label: 'Uptime' },
                { number: '10M+', label: 'Transações/dia' },
                { number: '24/7', label: 'Suporte Premium' }
            ],
            demo: 'https://premium-system-demo.com',
            code: 'https://github.com/carlosdev/premium-system'
        },
        6: {
            title: '💼 Sistema de Gestão Empresarial Premium',
            description: 'Plataforma completa de gestão para pequenos negócios com IA integrada - 5.000+ PMEs transformadas',
            image: 'https://via.placeholder.com/400x300/4c1d95/fbbf24?text=Business+Management',
            tech: ['React.js', 'Node.js', 'PostgreSQL', 'AI/ML', 'AWS'],
            features: [
                'Dashboard inteligente com IA preditiva',
                'Gestão financeira automatizada',
                'CRM integrado com automação',
                'Relatórios avançados em tempo real',
                'Integração com bancos e contabilidade',
                'Análise de performance e KPIs',
                'Sistema de vendas e estoque',
                'Gestão de funcionários e folha'
            ],
            stats: [
                { number: '5.000+', label: 'PMEs Transformadas' },
                { number: '85%', label: 'Redução de Custos' },
                { number: '300%', label: 'Aumento Produtividade' },
                { number: '99.8%', label: 'Satisfação Cliente' }
            ],
            demo: 'https://business-management-demo.com',
            code: 'https://github.com/carlosdev/business-management'
        },
        7: {
            title: '🛍️ E-commerce Inteligente Premium',
            description: 'Loja virtual com IA para pequenos negócios - 300% aumento nas vendas comprovado',
            image: 'https://via.placeholder.com/400x300/1e1b4b/fbbf24?text=Smart+Ecommerce',
            tech: ['Vue.js', 'Python', 'MongoDB', 'TensorFlow', 'Stripe'],
            features: [
                'Recomendações inteligentes com IA',
                'Chatbot de vendas automatizado',
                'Análise de comportamento do cliente',
                'Otimização automática de preços',
                'Marketing personalizado por IA',
                'Gestão inteligente de estoque',
                'Pagamentos globais integrados',
                'SEO automático e otimização'
            ],
            stats: [
                { number: '300%', label: 'Aumento nas Vendas' },
                { number: '2.500+', label: 'Lojas Ativas' },
                { number: '95%', label: 'Taxa de Conversão' },
                { number: '24/7', label: 'Vendas Automatizadas' }
            ],
            demo: 'https://smart-ecommerce-demo.com',
            code: 'https://github.com/carlosdev/smart-ecommerce'
        }
    };
    
    // Adicionar event listeners aos botões
    portfolioBtns.forEach((btn, index) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            openModal(projectsData[index]);
        });
    });
    
    // Fechar modal
    closeBtn.addEventListener('click', closeModal);
    
    // Fechar modal clicando fora
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });
    
    // Fechar modal com ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.style.display === 'block') {
            closeModal();
        }
    });
    
    function openModal(project) {
        // Verificar se o modal existe
        if (!modal) return;
        
        // Preencher dados do modal com verificações de null
        const modalTitle = document.getElementById('modalTitle');
        const modalDescription = document.getElementById('modalDescription');
        const modalImage = document.getElementById('modalImage');
        const modalDemo = document.getElementById('modalDemo');
        const modalCode = document.getElementById('modalCode');
        
        if (modalTitle) modalTitle.textContent = project.title;
        if (modalDescription) modalDescription.textContent = project.description;
        if (modalImage) modalImage.src = project.image;
        if (modalDemo) modalDemo.href = project.demo;
        if (modalCode) modalCode.href = project.code;
        
        // Preencher tecnologias
        const techContainer = document.getElementById('modalTech');
        if (techContainer) {
            techContainer.innerHTML = '';
            project.tech.forEach(tech => {
                const span = document.createElement('span');
                span.textContent = tech;
                techContainer.appendChild(span);
            });
        }
        
        // Preencher funcionalidades
        const featuresContainer = document.getElementById('modalFeatures');
        if (featuresContainer) {
            featuresContainer.innerHTML = '';
            project.features.forEach(feature => {
                const li = document.createElement('li');
                li.textContent = feature;
                featuresContainer.appendChild(li);
            });
        }
        
        // Preencher estatísticas
        const statsContainer = document.getElementById('modalStats');
        if (statsContainer) {
            statsContainer.innerHTML = '';
            project.stats.forEach(stat => {
                const statDiv = document.createElement('div');
                statDiv.className = 'stat-item';
                statDiv.innerHTML = `
                    <span class="stat-number">${stat.number}</span>
                    <span class="stat-label">${stat.label}</span>
                `;
                statsContainer.appendChild(statDiv);
            });
        }
        
        // Mostrar modal
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        
        // Adicionar animação de entrada
        setTimeout(() => {
            const modalContent = modal.querySelector('.modal-content');
            if (modalContent) {
                modalContent.style.transform = 'translateY(0)';
            }
        }, 10);
    }
    
    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}



// ===== NAVIGATION =====
function initializeNavigation() {
    const navLinks = document.querySelectorAll('.nav-link');
    
    // Update active nav link based on scroll position
    window.addEventListener('scroll', updateActiveNavLink);
    
    // Add click handlers for nav links
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href.startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    const header = document.querySelector('.header');
                    const headerHeight = header ? header.offsetHeight : 0;
                    const targetPosition = target.offsetTop - headerHeight;
                    
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
}

function updateActiveNavLink() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');
    const header = document.querySelector('.header');
    const headerHeight = header ? header.offsetHeight : 0;
    
    let currentSection = '';
    
    sections.forEach(section => {
        const sectionTop = section.offsetTop - headerHeight - 100;
        const sectionHeight = section.offsetHeight;
        
        if (window.scrollY >= sectionTop && window.scrollY < sectionTop + sectionHeight) {
            currentSection = section.getAttribute('id');
        }
    });
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === `#${currentSection}`) {
            link.classList.add('active');
        }
    });
}

// ===== MOBILE MENU =====
function initializeMobileMenu() {
    const hamburger = document.getElementById('hamburger');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileLinks = document.querySelectorAll('.mobile-link');
    
    // Verificar se os elementos existem antes de adicionar event listeners
    if (hamburger) {
        hamburger.addEventListener('click', toggleMobileMenu);
    }
    
    // Close mobile menu when clicking on a link
    if (mobileLinks.length > 0) {
        mobileLinks.forEach(link => {
            link.addEventListener('click', closeMobileMenu);
        });
    }
    
    // Close mobile menu when clicking outside
    if (hamburger && mobileMenu) {
        document.addEventListener('click', function(e) {
            if (!hamburger.contains(e.target) && !mobileMenu.contains(e.target)) {
                closeMobileMenu();
            }
        });
    }
}

function toggleMobileMenu() {
    const hamburger = document.getElementById('hamburger');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (hamburger) hamburger.classList.toggle('active');
    if (mobileMenu) mobileMenu.classList.toggle('active');
    
    // Add body scroll lock when menu is open
    if (mobileMenu && mobileMenu.classList.contains('active')) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = 'auto';
    }
}

function closeMobileMenu() {
    const hamburger = document.getElementById('hamburger');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (hamburger) hamburger.classList.remove('active');
    if (mobileMenu) mobileMenu.classList.remove('active');
    document.body.style.overflow = 'auto';
}



// ===== ANIMATIONS =====
function initializeAnimations() {
    // Animate elements on scroll
    const animatedElements = document.querySelectorAll('.fade-in');
    
    animationObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });
    
    animatedElements.forEach(element => {
        animationObserver.observe(element);
    });
}

// ===== COUNTERS =====
function initializeCounters() {
    const counters = document.querySelectorAll('.stat-number, .credibility-number, .roi-number');
    let countersAnimated = false;
    
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !countersAnimated) {
                countersAnimated = true;
                animateCounters();
            }
        });
    }, {
        threshold: 0.3
    });
    
    // Observe multiple sections
    const sections = ['.hero-stats', '.business-solutions', '.credibility-section', '.roi-section'];
    sections.forEach(selector => {
        const section = document.querySelector(selector);
        if (section) {
            counterObserver.observe(section);
        }
    });
}

function animateCounters() {
    const counters = document.querySelectorAll('.stat-number, .credibility-number, .roi-number');
    
    counters.forEach((counter, index) => {
        const target = parseInt(counter.getAttribute('data-target'));
        const duration = 2500; // 2.5 seconds for more dramatic effect
        const delay = index * 200; // Stagger animations
        
        // Add pulsing effect during animation
        counter.style.transform = 'scale(1)';
        counter.style.transition = 'transform 0.3s ease';
        
        setTimeout(() => {
            let current = 0;
            const increment = target / (duration / 16); // 60fps
            
            const updateCounter = () => {
                if (current < target) {
                    current += increment;
                    if (current > target) current = target;
                    
                    // Add pulse effect during counting
                    if (Math.floor(current) % 10 === 0) {
                        counter.style.transform = 'scale(1.1)';
                        setTimeout(() => {
                            counter.style.transform = 'scale(1)';
                        }, 100);
                    }
                    
                    // Format number based on target and add + for large numbers
                    if (target >= 1000) {
                        if (target >= 100000) {
                            counter.textContent = Math.floor(current / 1000) + 'K+';
                        } else {
                            counter.textContent = Math.floor(current).toLocaleString();
                        }
                    } else if (target === 98 || target === 100 || target === 300) {
                        counter.textContent = Math.floor(current) + '%';
                    } else {
                        counter.textContent = Math.floor(current);
                    }
                    
                    requestAnimationFrame(updateCounter);
                } else {
                    // Final value with special formatting
                    if (target >= 100000) {
                        counter.textContent = Math.floor(target / 1000) + 'K+';
                    } else if (target >= 1000) {
                        counter.textContent = target.toLocaleString();
                    } else if (target === 98 || target === 100 || target === 300) {
                        counter.textContent = target + '%';
                    } else {
                        counter.textContent = target;
                    }
                    
                    // Final pulse effect
                    counter.style.transform = 'scale(1.2)';
                    setTimeout(() => {
                        counter.style.transform = 'scale(1)';
                    }, 200);
                }
            };
            
            updateCounter();
        }, delay);
    });
}

// ===== SKILL BARS =====
function initializeSkillBars() {
    const skillBars = document.querySelectorAll('.skill-fill');
    let skillsAnimated = false;
    
    const skillObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !skillsAnimated) {
                skillsAnimated = true;
                animateSkillBars();
            }
        });
    }, {
        threshold: 0.5
    });
    
    if (skillBars.length > 0) {
        skillObserver.observe(skillBars[0].closest('.skill-bars'));
    }
}

function animateSkillBars() {
    const skillBars = document.querySelectorAll('.skill-fill');
    
    skillBars.forEach((bar, index) => {
        const width = bar.getAttribute('data-width');
        
        setTimeout(() => {
            bar.style.width = width + '%';
        }, index * 200); // Stagger animation
    });
}

// ===== SCROLL EFFECTS =====
function initializeScrollEffects() {
    // Parallax effect for floating shapes
    window.addEventListener('scroll', handleParallax);
    
    // Add scroll-based animations
    const scrollElements = document.querySelectorAll('.service-card, .portfolio-item, .timeline-item');
    
    scrollElements.forEach(element => {
        element.classList.add('fade-in');
        animationObserver.observe(element);
    });
    
    // Enhanced Timeline animations
    const timelineItems = document.querySelectorAll('.timeline-item');
    timelineItems.forEach((item, index) => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('animate');
                        // Add continuous animations to tech tags
                        const techTags = entry.target.querySelectorAll('.timeline-tech span');
                        techTags.forEach((tag, tagIndex) => {
                            tag.style.setProperty('--i', tagIndex);
                        });
                    }, index * 200);
                }
            });
        }, { threshold: 0.3 });
        observer.observe(item);
    });

    // Add continuous floating animation to timeline items
    function addTimelineFloatingEffect() {
        const timelineItems = document.querySelectorAll('.timeline-item.animate');
        timelineItems.forEach((item, index) => {
            const delay = index * 0.5;
            item.style.animationDelay = `${delay}s`;
        });
    }

    // Call floating effect after timeline items are animated
    setTimeout(addTimelineFloatingEffect, 2000);
}

function handleParallax() {
    const scrolled = window.pageYOffset;
    const shapes = document.querySelectorAll('.shape');
    
    shapes.forEach((shape, index) => {
        const speed = 0.5 + (index * 0.1);
        const yPos = -(scrolled * speed);
        shape.style.transform = `translateY(${yPos}px) rotate(${scrolled * 0.1}deg)`;
    });
}

// ===== SMOOTH SCROLLING =====
function initializeSmoothScrolling() {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href === '#' || href === '#inicio') return;
            
            e.preventDefault();
            const target = document.querySelector(href);
            
            if (target) {
                const header = document.querySelector('.header');
                const headerHeight = header ? header.offsetHeight : 0;
                const targetPosition = target.offsetTop - headerHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// ===== HEADER SCROLL EFFECT =====
function initializeHeaderScroll() {
    const header = document.querySelector('.header');
    let lastScrollY = window.scrollY;
    
    window.addEventListener('scroll', () => {
        const currentScrollY = window.scrollY;
        
        if (currentScrollY > 100) {
            header.style.background = 'rgba(10, 10, 15, 0.95)';
            header.style.backdropFilter = 'blur(20px)';
            header.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
        } else {
            header.style.background = 'rgba(10, 10, 15, 0.9)';
            header.style.boxShadow = 'none';
        }
        
        // Hide/show header on scroll
        if (currentScrollY > lastScrollY && currentScrollY > 200) {
            header.style.transform = 'translateY(-100%)';
        } else {
            header.style.transform = 'translateY(0)';
        }
        
        lastScrollY = currentScrollY;
    });
}

// ===== UTILITY FUNCTIONS =====
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    }
}

// ===== PERFORMANCE OPTIMIZATIONS =====
// Optimize scroll events
const optimizedScroll = throttle(() => {
    updateActiveNavLink();
    handleParallax();
}, 16); // 60fps

window.addEventListener('scroll', optimizedScroll);

// ===== LOADING ANIMATIONS =====
function showLoadingAnimation(element) {
    element.classList.add('loading');
    
    setTimeout(() => {
        element.classList.remove('loading');
    }, 1500);
}

// ===== INTERACTIVE EFFECTS =====
function initializeInteractiveEffects() {
    // Add hover effects to cards
    const cards = document.querySelectorAll('.service-card, .portfolio-item, .timeline-content');
    
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-10px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
    
    // Add ripple effect to buttons
    const buttons = document.querySelectorAll('.btn');
    
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
}

// ===== ACCESSIBILITY =====
function initializeAccessibility() {
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMobileMenu();
        }
        
        if (e.key === 'Tab') {
            document.body.classList.add('keyboard-navigation');
        }
    });
    
    document.addEventListener('mousedown', function() {
        document.body.classList.remove('keyboard-navigation');
    });
    
    // Focus management for mobile menu
    const mobileMenu = document.getElementById('mobileMenu');
    const focusableElements = mobileMenu.querySelectorAll('a, button');
    
    if (focusableElements.length > 0) {
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];
        
        mobileMenu.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                if (e.shiftKey) {
                    if (document.activeElement === firstElement) {
                        e.preventDefault();
                        lastElement.focus();
                    }
                } else {
                    if (document.activeElement === lastElement) {
                        e.preventDefault();
                        firstElement.focus();
                    }
                }
            }
        });
    }
}

// ===== ERROR HANDLING =====
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
    // Graceful degradation - ensure basic functionality still works
});

// ===== INITIALIZE ADDITIONAL FEATURES =====
document.addEventListener('DOMContentLoaded', function() {
    // Initialize additional features after DOM is loaded
    setTimeout(() => {
        initializeInteractiveEffects();
        initializeAccessibility();
    }, 100);
});

// ===== RESIZE HANDLER =====
window.addEventListener('resize', debounce(() => {
    // Handle responsive adjustments
    if (window.innerWidth > 768) {
        closeMobileMenu();
    }
}, 250));

// ===== PRELOADER =====
function hidePreloader() {
    const preloader = document.querySelector('.preloader');
    if (preloader) {
        preloader.style.opacity = '0';
        setTimeout(() => {
            preloader.style.display = 'none';
        }, 300);
    }
}

// Hide preloader when page is fully loaded
window.addEventListener('load', hidePreloader);

// ===== TESTIMONIAL CAROUSEL =====
function initTestimonialCarousel() {
    const testimonials = document.querySelectorAll('.testimonial-item');
    const dots = document.querySelectorAll('.dot');
    let currentSlide = 0;
    
    if (testimonials.length === 0) return;
    
    function showSlide(index) {
        testimonials.forEach((testimonial, i) => {
            testimonial.classList.remove('active');
            if (i === index) {
                testimonial.classList.add('active');
            }
        });
        
        dots.forEach((dot, i) => {
            dot.classList.remove('active');
            if (i === index) {
                dot.classList.add('active');
            }
        });
    }
    
    function nextSlide() {
        currentSlide = (currentSlide + 1) % testimonials.length;
        showSlide(currentSlide);
    }
    
    // Auto-advance carousel
    setInterval(nextSlide, 4000);
    
    // Dot navigation
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentSlide = index;
            showSlide(currentSlide);
        });
    });
}

// ===== CREDIBILITY ANIMATIONS =====
function initCredibilityAnimations() {
    const credibilityNumbers = document.querySelectorAll('.credibility-number');
    const techFills = document.querySelectorAll('.tech-fill');
    
    // Animate credibility numbers
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px 0px -100px 0px'
    };
    
    const credibilityObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = parseInt(entry.target.dataset.target);
                animateNumber(entry.target, 0, target, 2000);
                credibilityObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    credibilityNumbers.forEach(number => {
        credibilityObserver.observe(number);
    });
    
    // Animate tech skill bars
    const techObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const fills = entry.target.querySelectorAll('.tech-fill');
                fills.forEach(fill => {
                    const width = fill.style.width;
                    fill.style.width = '0%';
                    setTimeout(() => {
                        fill.style.width = width;
                    }, 200);
                });
                techObserver.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    const techShowcase = document.querySelector('.tech-showcase');
    if (techShowcase) {
        techObserver.observe(techShowcase);
    }
}

// ===== ENHANCED 3D BACKGROUND =====
function initEnhanced3DBackground() {
    const animatedBg = document.querySelector('.animated-background');
    if (!animatedBg) return;
    
    // Add more 3D shapes
    for (let i = 6; i <= 15; i++) {
        const shape = document.createElement('div');
        shape.className = `shape shape-${i}`;
        
        // Randomly assign shape types
        const shapeTypes = ['', 'cube', 'triangle'];
        const randomType = shapeTypes[Math.floor(Math.random() * shapeTypes.length)];
        if (randomType) shape.classList.add(randomType);
        
        // Random positioning and sizing
        const size = Math.random() * 100 + 50;
        const top = Math.random() * 100;
        const left = Math.random() * 100;
        const delay = Math.random() * -30;
        
        shape.style.cssText = `
            width: ${size}px;
            height: ${size}px;
            top: ${top}%;
            left: ${left}%;
            animation-delay: ${delay}s;
        `;
        
        animatedBg.appendChild(shape);
    }
    
    // Add particle system
    for (let i = 0; i < 20; i++) {
        const particle = document.createElement('div');
        particle.className = 'bg-particles';
        
        const left = Math.random() * 100;
        const delay = Math.random() * -15;
        const duration = Math.random() * 10 + 10;
        
        particle.style.cssText = `
            left: ${left}%;
            animation-delay: ${delay}s;
            animation-duration: ${duration}s;
        `;
        
        animatedBg.appendChild(particle);
    }
    
    // Mouse parallax effect
    document.addEventListener('mousemove', (e) => {
        const shapes = animatedBg.querySelectorAll('.shape');
        const mouseX = e.clientX / window.innerWidth;
        const mouseY = e.clientY / window.innerHeight;
        
        shapes.forEach((shape, index) => {
            const speed = (index % 3 + 1) * 0.5;
            const x = (mouseX - 0.5) * speed * 20;
            const y = (mouseY - 0.5) * speed * 20;
            
            shape.style.transform += ` translate(${x}px, ${y}px)`;
        });
    });
}

// ===== ENHANCED NUMBER ANIMATION =====
function animateNumber(element, start, end, duration) {
    const startTime = performance.now();
    const range = end - start;
    
    function updateNumber(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        // Easing function for smooth animation
        const easeOutQuart = 1 - Math.pow(1 - progress, 4);
        const current = Math.floor(start + (range * easeOutQuart));
        
        element.textContent = current.toLocaleString();
        
        if (progress < 1) {
            requestAnimationFrame(updateNumber);
        } else {
            element.textContent = end.toLocaleString();
        }
    }
    
    requestAnimationFrame(updateNumber);
}

// ===== CONTACT FORM =====
function initContactForm() {
    const form = document.querySelector('.contact-form');
    const submitBtn = document.querySelector('.submit-btn');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validar campos
            const name = form.querySelector('input[type="text"]').value.trim();
            const email = form.querySelector('input[type="email"]').value.trim();
            const message = form.querySelector('textarea').value.trim();
            
            if (!name || !email || !message) {
                showNotification('Por favor, preencha todos os campos!', 'error');
                return;
            }
            
            if (!isValidEmail(email)) {
                showNotification('Por favor, insira um email válido!', 'error');
                return;
            }
            
            // Simular envio
            submitBtn.textContent = 'Enviando...';
            submitBtn.disabled = true;
            
            setTimeout(() => {
                showNotification('Mensagem enviada com sucesso! Retornarei em breve.', 'success');
                form.reset();
                submitBtn.textContent = 'Enviar Mensagem';
                submitBtn.disabled = false;
            }, 2000);
        });
    }
    
    // Adicionar efeitos aos campos do formulário
    const inputs = document.querySelectorAll('.contact-form input, .contact-form textarea');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
        });
    });
}

// Função para validar email
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Sistema de notificações
function showNotification(message, type = 'info') {
    // Remover notificação existente
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }
    
    // Criar nova notificação
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-icon">${type === 'success' ? '✓' : type === 'error' ? '✗' : 'ℹ'}</span>
            <span class="notification-message">${message}</span>
            <button class="notification-close">×</button>
        </div>
    `;
    
    // Adicionar ao body
    document.body.appendChild(notification);
    
    // Animar entrada
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Fechar automaticamente
    setTimeout(() => {
        closeNotification(notification);
    }, 5000);
    
    // Fechar ao clicar no X
    notification.querySelector('.notification-close').addEventListener('click', () => {
        closeNotification(notification);
    });
}

function closeNotification(notification) {
    notification.classList.remove('show');
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 300);
}

// ===== BUTTON EFFECTS =====
function initButtonEffects() {
    const buttons = document.querySelectorAll('button, .btn, .cta-button, .portfolio-btn, .premium-btn');
    
    buttons.forEach(button => {
        // Efeitos de hover
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.3)';
        });
        
        button.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '';
        });
        
        // Efeitos de clique
        button.addEventListener('mousedown', function() {
            this.style.transform = 'translateY(0) scale(0.95)';
            
            // Criar efeito ripple
            createRippleEffect(this, event);
        });
        
        button.addEventListener('mouseup', function() {
            this.style.transform = 'translateY(-2px) scale(1)';
        });
        
        // Efeito de foco para acessibilidade
        button.addEventListener('focus', function() {
            this.style.outline = '2px solid #667eea';
            this.style.outlineOffset = '2px';
        });
        
        button.addEventListener('blur', function() {
            this.style.outline = 'none';
        });
    });
    
    // Adicionar funcionalidade aos botões de navegação
    const navButtons = document.querySelectorAll('.nav-link');
    navButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Remover classe ativa de todos os botões
            navButtons.forEach(btn => btn.classList.remove('active'));
            // Adicionar classe ativa ao botão clicado
            this.classList.add('active');
            
            // Smooth scroll para a seção
            const targetId = this.getAttribute('href');
            if (targetId && targetId.startsWith('#')) {
                e.preventDefault();
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
}

// Função para criar efeito ripple nos botões
function createRippleEffect(button, event) {
    const ripple = document.createElement('span');
    const rect = button.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;
    
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = x + 'px';
    ripple.style.top = y + 'px';
    ripple.classList.add('ripple');
    
    button.style.position = 'relative';
    button.style.overflow = 'hidden';
    button.appendChild(ripple);
    
    setTimeout(() => {
        ripple.remove();
    }, 600);
}

// ===== PREMIUM PROJECTS SYSTEM =====
// Adicionar dados dos projetos premium
const premiumProjects = {
    'sistema-gestao': {
        title: 'Sistema de Gestão Empresarial Avançado',
        description: 'Plataforma completa de gestão empresarial com IA integrada, analytics avançados e automação de processos.',
        image: 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
        technologies: ['React.js', 'Node.js', 'PostgreSQL', 'TensorFlow', 'AWS'],
        features: [
            'Dashboard Inteligente com IA',
            'Automação de Processos',
            'Analytics Preditivos',
            'Integração Multi-plataforma',
            'Segurança Avançada'
        ],
        stats: {
            'Eficiência': '95%',
            'Usuários': '10K+',
            'Uptime': '99.9%'
        },
        demoUrl: '#',
        codeUrl: '#'
    },
    'ecommerce-inteligente': {
        title: 'E-commerce Inteligente Premium',
        description: 'Plataforma de e-commerce com IA para recomendações personalizadas, analytics avançados e automação completa.',
        image: 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2340&q=80',
        technologies: ['Vue.js', 'Python', 'MongoDB', 'Stripe', 'Docker'],
        features: [
            'IA para Recomendações',
            'Checkout Inteligente',
            'Analytics de Vendas',
            'Gestão de Inventário',
            'Marketing Automatizado'
        ],
        stats: {
            'Conversão': '87%',
            'Vendas': '500K+',
            'Performance': '98%'
        },
        demoUrl: '#',
        codeUrl: '#'
    }
};

// Sistema de partículas para projetos premium
class PremiumParticleSystem {
    constructor(container) {
        this.container = container;
        this.particles = [];
        this.canvas = null;
        this.ctx = null;
        this.animationId = null;
        this.init();
    }

    init() {
        this.canvas = document.createElement('canvas');
        this.canvas.style.position = 'absolute';
        this.canvas.style.top = '0';
        this.canvas.style.left = '0';
        this.canvas.style.width = '100%';
        this.canvas.style.height = '100%';
        this.canvas.style.pointerEvents = 'none';
        this.canvas.style.zIndex = '-2';
        this.canvas.style.opacity = '0.7';
        
        this.container.appendChild(this.canvas);
        this.ctx = this.canvas.getContext('2d');
        
        this.resize();
        this.createParticles();
        this.animate();
        
        window.addEventListener('resize', () => this.resize());
    }

    resize() {
        const rect = this.container.getBoundingClientRect();
        this.canvas.width = rect.width;
        this.canvas.height = rect.height;
    }

    createParticles() {
        const particleCount = 15;
        for (let i = 0; i < particleCount; i++) {
            this.particles.push({
                x: Math.random() * this.canvas.width,
                y: Math.random() * this.canvas.height,
                vx: (Math.random() - 0.5) * 0.5,
                vy: (Math.random() - 0.5) * 0.5,
                size: Math.random() * 3 + 1,
                opacity: Math.random() * 0.5 + 0.2,
                color: Math.random() > 0.5 ? 'rgba(138, 43, 226, ' : 'rgba(75, 0, 130, '
            });
        }
    }

    animate() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        
        this.particles.forEach(particle => {
            particle.x += particle.vx;
            particle.y += particle.vy;
            
            if (particle.x < 0 || particle.x > this.canvas.width) particle.vx *= -1;
            if (particle.y < 0 || particle.y > this.canvas.height) particle.vy *= -1;
            
            this.ctx.beginPath();
            this.ctx.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2);
            this.ctx.fillStyle = particle.color + particle.opacity + ')';
            this.ctx.fill();
            
            // Conectar partículas próximas
            this.particles.forEach(otherParticle => {
                const dx = particle.x - otherParticle.x;
                const dy = particle.y - otherParticle.y;
                const distance = Math.sqrt(dx * dx + dy * dy);
                
                if (distance < 100) {
                    this.ctx.beginPath();
                    this.ctx.moveTo(particle.x, particle.y);
                    this.ctx.lineTo(otherParticle.x, otherParticle.y);
                    this.ctx.strokeStyle = `rgba(138, 43, 226, ${0.1 * (1 - distance / 100)})`;
                    this.ctx.stroke();
                }
            });
        });
        
        this.animationId = requestAnimationFrame(() => this.animate());
    }

    destroy() {
        if (this.animationId) {
            cancelAnimationFrame(this.animationId);
        }
        if (this.canvas && this.canvas.parentNode) {
            this.canvas.parentNode.removeChild(this.canvas);
        }
    }
}

// Sistema de efeitos interativos premium
class PremiumInteractionSystem {
    constructor() {
        this.particleSystems = new Map();
        this.init();
    }

    init() {
        this.setupPremiumCards();
        this.setupRippleEffects();
        this.setupParallaxEffects();
        this.setupWhatsAppLinks();
        setupFloatingWhatsApp();
        this.setupCounterAnimations();
    }

    setupPremiumCards() {
        const premiumCards = document.querySelectorAll('.premium-project-card');
        
        premiumCards.forEach(card => {
            // Adicionar sistema de partículas
            const particleSystem = new PremiumParticleSystem(card);
            this.particleSystems.set(card, particleSystem);
            
            // Efeito de inclinação 3D
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                const rotateX = (y - centerY) / 10;
                const rotateY = (centerX - x) / 10;
                
                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(20px)`;
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateZ(0px)';
            });
            
            // Animação de entrada
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animation = 'premiumCardEnter 1s ease-out forwards';
                    }
                });
            }, { threshold: 0.1 });
            
            observer.observe(card);
        });
    }

    setupRippleEffects() {
        const buttons = document.querySelectorAll('.premium-btn');
        
        buttons.forEach(button => {
            button.addEventListener('click', (e) => {
                const rect = button.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const ripple = document.createElement('span');
                ripple.style.position = 'absolute';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.style.width = '0';
                ripple.style.height = '0';
                ripple.style.borderRadius = '50%';
                ripple.style.background = 'rgba(255, 255, 255, 0.6)';
                ripple.style.transform = 'translate(-50%, -50%)';
                ripple.style.animation = 'rippleEffect 0.6s ease-out';
                ripple.style.pointerEvents = 'none';
                
                button.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });
    }

    setupParallaxEffects() {
        const premiumSection = document.querySelector('.premium-projects-section');
        if (!premiumSection) return;
        
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            
            premiumSection.style.transform = `translateY(${rate}px)`;
        });
    }

    // Configurar links do WhatsApp para os pacotes
    setupWhatsAppLinks() {
        const whatsappNumber = '5543996593590'; // Número do WhatsApp
        
        const packages = {
            'package-personalizado': {
                name: 'Personalizado',
                price: 'A partir de R$250',
                features: 'Design UI/UX Exclusivo, Sistema personalizado, Integração de APIs, Pequenas alterações, Documentação Completa'
            },
            'package-enterprise': {
                name: 'Enterprise Pro',
                price: 'A partir de R$800',
                features: 'Tudo do Personalizado, Integração ao Back-End, Banco de Dados, Deploy em Produção, Suporte 30 dias'
            },
            'package-ai': {
                name: 'AI Revolution',
                price: 'A partir de R$1.500',
                features: 'Tudo do Enterprise Pro, Machine Learning, Automação Python, IA Personalizada, Analytics Avançado'
            },
            'package-transformacao': {
                name: 'Transformação Digital',
                price: 'R$ 2.400',
                features: 'Consultoria Estratégica, Arquitetura Completa, Migração de Sistemas, Treinamento Equipe, Suporte 6 meses'
            },
            'package-completo': {
                name: 'Plano Completo',
                price: 'R$ 3.500',
                features: 'Landing Pages, A/B Testing, Analytics Setup, Otimização SEO, Relatórios Mensais'
            },
            'package-corporacao': {
                name: 'Corporação',
                price: 'R$ 5.200',
                features: 'Solução Enterprise, Multi-plataforma, Segurança Avançada, Escalabilidade, Suporte 24/7'
            }
        };

        Object.keys(packages).forEach(packageId => {
            const button = document.getElementById(packageId);
            if (button) {
                button.addEventListener('click', () => {
                    const pkg = packages[packageId];
                    const message = `Olá Carlos! 👋\n\nTenho interesse no plano *${pkg.name}* (${pkg.price})\n\n📋 *Recursos inclusos:*\n${pkg.features}\n\nGostaria de saber mais detalhes e como podemos começar o projeto!\n\nObrigado! 🚀`;
                    
                    const whatsappUrl = `https://wa.me/${whatsappNumber}?text=${encodeURIComponent(message)}`;
                    window.open(whatsappUrl, '_blank');
                });
            }
        });
    }

    setupCounterAnimations() {
        const statNumbers = document.querySelectorAll('.stat-number');
        
        const animateCounter = (element, target) => {
            let current = 0;
            const increment = target / 100;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current) + (target.toString().includes('%') ? '%' : target.toString().includes('K') ? 'K+' : '');
            }, 20);
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const target = parseInt(entry.target.textContent.replace(/[^0-9]/g, ''));
                    animateCounter(entry.target, target);
                }
            });
        }, { threshold: 0.5 });
        
        statNumbers.forEach(stat => observer.observe(stat));
    }

    destroy() {
        this.particleSystems.forEach(system => system.destroy());
        this.particleSystems.clear();
    }
}

// Inicializar sistema premium quando o DOM estiver carregado
// Função para configurar o botão de contato flutuante
function setupFloatingWhatsApp() {
    const floatingBtn = document.getElementById('floating-whatsapp');
    
    if (floatingBtn) {
        floatingBtn.addEventListener('click', function() {
            const phoneNumber = '5543996593590';
            const message = encodeURIComponent('Olá! Gostaria de saber mais sobre seus serviços de desenvolvimento. Vim através do seu site.');
            const whatsappUrl = `https://wa.me/${phoneNumber}?text=${message}`;
            
            window.open(whatsappUrl, '_blank');
        });
        
        // Adicionar efeito de entrada suave
        setTimeout(() => {
            floatingBtn.style.opacity = '1';
            floatingBtn.style.transform = 'scale(1)';
        }, 1000);
    }
}

let premiumSystem;
document.addEventListener('DOMContentLoaded', () => {
    premiumSystem = new PremiumInteractionSystem();
    premiumSystem.init();
});// ===== EXPORT FOR TESTING =====
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        toggleMobileMenu,
        animateCounters,
        animateSkillBars
    };
}
