/* =========================
   ASTRAFLUX — SCRIPT CORE
   ========================= */

(() => {
    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

    /* Year */
    const yearEl = $('#year');
    if (yearEl) yearEl.textContent = new Date().getFullYear();

    /* Scroll Progress */
    const progress = $('.progress');
    const updateProgress = () => {
        const h = document.documentElement;
        const scrolled = (h.scrollTop) / (h.scrollHeight - h.clientHeight);
        progress.style.width = `${Math.max(0, Math.min(1, scrolled)) * 100}%`;
    };
    updateProgress();
    window.addEventListener('scroll', updateProgress, { passive: true });

    /* Theme Toggle (desktop & mobile) */
    const THEME_KEY = 'astraflux:theme';
    const applyStoredTheme = () => {
        const stored = localStorage.getItem(THEME_KEY);
        if (stored === 'light') document.body.classList.add('light');
    };
    applyStoredTheme();
    const toggleTheme = () => {
        document.body.classList.toggle('light');
        localStorage.setItem(THEME_KEY, document.body.classList.contains('light') ? 'light' : 'dark');
    };
    $('#theme-toggle') ? .addEventListener('click', toggleTheme);
    $('#theme-toggle-mobile') ? .addEventListener('click', toggleTheme);

    /* Drawer (mobile menu) */
    const hamburger = $('#hamburger');
    const drawer = $('#drawer');
    const bodyScrollLock = (lock) => {
        if (lock) {
            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
        } else {
            document.documentElement.style.overflow = '';
            document.body.style.overflow = '';
        }
    };
    const closeDrawer = () => {
        drawer ? .classList.remove('open');
        hamburger ? .classList.remove('active');
        if (hamburger) hamburger.setAttribute('aria-expanded', 'false');
        if (drawer) drawer.setAttribute('aria-hidden', 'true');
        bodyScrollLock(false);
    };
    const openDrawer = () => {
        drawer ? .classList.add('open');
        hamburger ? .classList.add('active');
        if (hamburger) hamburger.setAttribute('aria-expanded', 'true');
        if (drawer) drawer.setAttribute('aria-hidden', 'false');
        bodyScrollLock(true);
    };
    hamburger ? .addEventListener('click', () => {
        if (drawer.classList.contains('open')) closeDrawer();
        else openDrawer();
    });
    $$('.drawer-nav a', drawer).forEach(a => a.addEventListener('click', closeDrawer));

    /* Smooth anchor (header links) */
    $$('a[href^="#"]').forEach(a => {
        a.addEventListener('click', (e) => {
            const id = a.getAttribute('href');
            if (!id || id === '#') return;
            const el = document.querySelector(id);
            if (el) {
                e.preventDefault();
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    /* Reveal on scroll (IntersectionObserver) */
    const revealTargets = [
        '.section-head', '.card', '.price', '.about-card', '.testimonial',
        '.hero-copy', '.hero-visual', '.trust', '.slides .slide'
    ];
    $$(revealTargets.join(',')).forEach(el => el.classList.add('will-reveal'));
    const io = new IntersectionObserver((entries) => {
        for (const e of entries) {
            if (e.isIntersecting) {
                e.target.classList.add('revealed');
                e.target.classList.remove('will-reveal');
                io.unobserve(e.target);
            }
        }
    }, { threshold: .18 });
    $$('.will-reveal').forEach(el => io.observe(el));

    /* KPI Counters */
    const easeOutCubic = t => 1 - Math.pow(1 - t, 3);
    const animateNumber = (el, to, duration = 1200, suffix = '') => {
        const from = 0;
        const start = performance.now();
        const step = now => {
            const p = Math.min(1, (now - start) / duration);
            const val = Math.round(easeOutCubic(p) * to);
            el.textContent = `${val}${suffix}`;
            if (p < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    };
    const kpiEls = $$('.kpi-num');
    const kpiIo = new IntersectionObserver((entries) => {
        entries.forEach(ent => {
            if (ent.isIntersecting) {
                const to = parseInt(ent.target.dataset.count || '0', 10);
                animateNumber(ent.target, to);
                kpiIo.unobserve(ent.target);
            }
        });
    }, { threshold: .6 });
    kpiEls.forEach(el => kpiIo.observe(el));

    /* Metrics (ROAS/CPL/CAC) playful live tick */
    const roasEl = $('[data-animate="roas"]');
    const cplEl = $('[data-animate="cpl"]');
    const cacEl = $('[data-animate="cac"]');
    const fmtMoney = n => n.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL', minimumFractionDigits: 2 });
    let tickTimer;
    const startMetrics = () => {
        const tick = () => {
            const roas = (3 + Math.random() * 3.2).toFixed(1);
            const cpl = 6 + Math.random() * 18;
            const cac = 30 + Math.random() * 50;
            if (roasEl) roasEl.textContent = `${roas}x`;
            if (cplEl) cplEl.textContent = fmtMoney(cpl);
            if (cacEl) cacEl.textContent = fmtMoney(cac);
        };
        tick();
        tickTimer = setInterval(tick, 2200);
    };
    startMetrics();

    /* Slider (Cases) */
    const slider = $('.slider');
    if (slider) {
        const slidesWrap = $('.slides', slider);
        const slides = $$('.slide', slidesWrap);
        const prevBtn = $('.slider-arrow.prev', slider);
        const nextBtn = $('.slider-arrow.next', slider);
        const dotsWrap = $('.slider-dots', slider);

        if (slidesWrap && slides.length > 0 && dotsWrap) {
            let index = 0,
                isDragging = false,
                startX = 0,
                startScroll = 0;

            const goTo = (i) => {
                index = (i + slides.length) % slides.length;
                slidesWrap.style.transition = 'none';
                slidesWrap.style.transform = `translateX(-${index * 100}%)`;
                $$('button', dotsWrap).forEach((b, bi) => b.classList.toggle('active', bi === index));
            };

            // Build dots
            slides.forEach((_, i) => {
                const b = document.createElement('button');
                b.setAttribute('aria-label', `Ir para slide ${i+1}`);
                b.addEventListener('click', () => goTo(i));
                dotsWrap.appendChild(b);
            });

            goTo(0);
            prevBtn ? .addEventListener('click', () => goTo(index - 1));
            nextBtn ? .addEventListener('click', () => goTo(index + 1));
            // Funcionalidade de drag/swipe removida para evitar bugs
        }
    }

    /* 3D hover for cards (light tilt) */
    const tiltables = $$('.card.service, .slide.card, .price');
    tiltables.forEach(card => {
        let rAF = null;
        const onMove = (e) => {
            const rect = card.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width;
            const y = (e.clientY - rect.top) / rect.height;
            const rx = (y - .5) * -8;
            const ry = (x - .5) * 10;
            if (rAF) cancelAnimationFrame(rAF);
            rAF = requestAnimationFrame(() => {
                card.style.transform = `perspective(800px) rotateX(${rx}deg) rotateY(${ry}deg) translateY(-2px)`;
            });
        };
        const reset = () => { card.style.transform = ''; };
        card.addEventListener('mousemove', onMove);
        card.addEventListener('mouseleave', reset);
    });

    /* Remove Skeleton after "load" */
    window.addEventListener('load', () => {
        setTimeout(() => $$('.skeleton').forEach(el => el.classList.remove('skeleton')), 350);
    });

    /* Código do botão voltar ao topo removido */

    /* Modal (dialog) open/close */
    const openers = $$('.open-modal');
    const modal = $('#lead-modal');
    const closeBtn = $('.modal-close', modal);
    openers.forEach(btn => btn.addEventListener('click', () => {
        if (typeof modal.showModal === 'function') modal.showModal();
        else modal.setAttribute('open', 'open'); // fallback
    }));
    closeBtn ? .addEventListener('click', (e) => {
        e.preventDefault();
        modal.close ? modal.close() : modal.removeAttribute('open');
    });
    modal ? .addEventListener('click', (e) => {
        const dialogRect = $('.modal-body', modal) ? .getBoundingClientRect();
        if (!dialogRect) return;
        const inside = e.clientX >= dialogRect.left && e.clientX <= dialogRect.right &&
            e.clientY >= dialogRect.top && e.clientY <= dialogRect.bottom;
        if (!inside) modal.close ? modal.close() : modal.removeAttribute('open');
    });

    /* Contact form - WhatsApp redirect */
    $('.contact-form') ? .addEventListener('submit', (e) => {
        e.preventDefault();

        const form = e.target;
        const formData = new FormData(form);

        // Get form data
        const nome = formData.get('nome') || 'Visitante';
        const email = formData.get('email') || '';
        const telefone = formData.get('telefone') || '';
        const orcamento = formData.get('orcamento') || '';
        const mensagem = formData.get('mensagem') || 'Gostaria de saber mais sobre seus serviços de tráfego pago.';

        // Create WhatsApp message
        let whatsappMsg = `Olá! Sou ${nome}.\n\n${mensagem}`;

        if (email) whatsappMsg += `\n\nEmail: ${email}`;
        if (telefone) whatsappMsg += `\nTelefone: ${telefone}`;
        if (orcamento) whatsappMsg += `\nOrçamento: ${orcamento}`;

        // Redirect to WhatsApp
        const whatsappUrl = `https://wa.me/5543996593590?text=${encodeURIComponent(whatsappMsg)}`;
        window.open(whatsappUrl, '_blank');

        // Reset form
        form.reset();
    });

    /* Sticky header subtle shadow on scroll */
    let headerShadowApplied = false;
    const header = $('.site-header');
    const hdrShadow = () => {
        if (window.scrollY > 10 && !headerShadowApplied) {
            header.style.boxShadow = '0 10px 30px rgba(0,0,0,.25)';
            headerShadowApplied = true;
        } else if (window.scrollY <= 10 && headerShadowApplied) {
            header.style.boxShadow = 'none';
            headerShadowApplied = false;
        }
    };
    window.addEventListener('scroll', hdrShadow, { passive: true });
    hdrShadow();

    /* Background Particles + Constellations */
    const canvas = $('#bg-dots');
    const ctx = canvas.getContext('2d', { alpha: true });
    let W = canvas.width = window.innerWidth;
    let H = canvas.height = window.innerHeight;
    const DPR = Math.min(2, window.devicePixelRatio || 1);
    canvas.width = W * DPR;
    canvas.height = H * DPR;
    ctx.scale(DPR, DPR);

    let particles = [];
    const PCOUNT = Math.min(140, Math.floor((W * H) / 14000));
    const rand = (a, b) => a + Math.random() * (b - a);
    const initParticles = () => {
        particles = Array.from({ length: PCOUNT }).map(() => ({
            x: rand(0, W),
            y: rand(0, H),
            vx: rand(-.25, .25),
            vy: rand(-.25, .25),
            r: rand(1.0, 2.4),
            o: rand(.35, .9)
        }));
    };
    initParticles();

    const draw = () => {
        ctx.clearRect(0, 0, W, H);
        // points
        ctx.fillStyle = '#20d3ff';
        for (const p of particles) {
            ctx.globalAlpha = p.o;
            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fill();
        }
        // lines
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const a = particles[i],
                    b = particles[j];
                const dx = a.x - b.x,
                    dy = a.y - b.y;
                const dist = Math.hypot(dx, dy);
                if (dist < 120) {
                    const alpha = 1 - (dist / 120);
                    ctx.globalAlpha = alpha * .25;
                    ctx.strokeStyle = '#7c4dff';
                    ctx.beginPath();
                    ctx.moveTo(a.x, a.y);
                    ctx.lineTo(b.x, b.y);
                    ctx.stroke();
                }
            }
        }
        // motion
        for (const p of particles) {
            p.x += p.vx;
            p.y += p.vy;
            if (p.x < -20) p.x = W + 20;
            if (p.x > W + 20) p.x = -20;
            if (p.y < -20) p.y = H + 20;
            if (p.y > H + 20) p.y = -20;
        }
        requestAnimationFrame(draw);
    };
    draw();

    /* Resize handling (debounced) */
    let rto;
    window.addEventListener('resize', () => {
        clearTimeout(rto);
        rto = setTimeout(() => {
            W = canvas.width = window.innerWidth;
            H = canvas.height = window.innerHeight;
            canvas.width = W * DPR;
            canvas.height = H * DPR;
            ctx.scale(DPR, DPR);
            initParticles();
        }, 150);
    });

})();

/* =========================
   ADD-ON: RELATÓRIOS ECOM
   ========================= */
(() => {
    const $ = (s, r = document) => r.querySelector(s);
    const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
    const fmtMoney = n => n.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    const pct = n => `${(n*100).toFixed(2)}%`;

    /* -------- Dataset sintético (convincente) -------- */
    const rnd = (a, b) => a + Math.random() * (b - a);
    const pick = arr => arr[Math.floor(Math.random() * arr.length)];
    const todayISO = () => new Date().toISOString().slice(0, 10);

    const PRODUCTS = [
        'Notebook HyperX i7', 'Mouse Óptico Pro', 'Teclado Mecânico RGB',
        'Headset 7.1 XWave', 'Monitor 27” 144Hz', 'SSD NVMe 1TB',
        'Placa de Vídeo 8GB', 'Webcam 1080p', 'Cadeira Gamer X',
        'Roteador Wi-Fi 6', 'Fonte 650W 80+ Bronze', 'Gabinete ATX ARGB'
    ];

    const CHANNELS = ['meta', 'google', 'organico', 'email'];
    const DEVICES = ['desktop', 'mobile', 'tablet'];

    // Gera ~16 campanhas (8 Meta/Google, 4 Orgânico/Email)
    const buildCampaigns = () => {
        const c = [];
        const namesMeta = ['Prospecting UGC', 'Retarget ViewContent', 'Lookalike 2%', 'DPAs Dynamic'];
        const namesGoogle = ['Search Brand', 'Non-Brand Generic', 'YouTube Remarketing', 'Smart Shopping'];
        const namesOrgs = ['SEO Landing Tech', 'Blog Comparativos', 'Guia de Compra'];
        const namesEmail = ['Fluxo Boas-vindas', 'Carrinho Abandonado', 'Promoções Semanais'];
        const push = (arr, canal) => arr.forEach(n => c.push({ canal, name: n, base: Math.random() }));
        push(namesMeta, 'meta');
        push(namesGoogle, 'google');
        push(namesOrgs, 'organico');
        push(namesEmail, 'email');
        return c.slice(0, 16).map((x, i) => ({ id: i + 1, ...x, device: pick(DEVICES) }));
    };

    // Gera série diária de 90 dias por campanha
    const generateData = () => {
        const campaigns = buildCampaigns();
        const days = 90;
        const start = new Date();
        start.setDate(start.getDate() - (days - 1));
        const all = [];
        for (const camp of campaigns) {
            let trend = rnd(.9, 1.1); // tendência sutil
            for (let d = 0; d < days; d++) {
                const day = new Date(start.getTime());
                day.setDate(start.getDate() + d);
                const iso = day.toISOString().slice(0, 10);
                const season = 1 + 0.15 * Math.sin(d / 9); // sazonalidade leve
                // parâmetros por canal
                const m = camp.canal === 'meta' ? { cpc: rnd(0.8, 1.8), conv: rnd(0.015, 0.028) } :
                    camp.canal === 'google' ? { cpc: rnd(0.9, 2.2), conv: rnd(0.020, 0.035) } :
                    camp.canal === 'organico' ? { cpc: 0, conv: rnd(0.012, 0.022) } : { cpc: rnd(0.2, 0.6), conv: rnd(0.010, 0.020) }; // email
                const impressions = Math.round(800 * season * trend * (1 + camp.base));
                const clicks = Math.max(1, Math.round(impressions * rnd(0.01, 0.03)));
                const spend = camp.canal === 'organico' ? 0 : clicks * m.cpc * rnd(.9, 1.15);
                const conversions = Math.round(clicks * m.conv * rnd(.85, 1.15));
                const aov = rnd(180, 480); // ticket médio tech
                const revenue = conversions * aov * rnd(.95, 1.05);
                const status = Math.random() < 0.92 ? 'on' : (Math.random() < 0.6 ? 'paused' : 'off');
                // aloca receita por produto (para gráfico top produtos)
                const product = pick(PRODUCTS);
                all.push({
                    date: iso,
                    campanha: camp.name,
                    canal: camp.canal,
                    disp: camp.device,
                    status,
                    impr: impressions,
                    clicks,
                    spend,
                    conv: conversions,
                    rev: revenue,
                    product
                });
            }
        }
        return all;
    };

    const DATA = generateData();

    /* -------- Helpers de filtro e agregação -------- */
    const getRange = () => {
        const s = $('#flt-start').value,
            e = $('#flt-end').value;
        return { start: s, end: e };
    };
    const setDefaultRange = (days = 30) => {
        const startEl = $('#flt-start');
        const endEl = $('#flt-end');
        if (!startEl || !endEl) return; // Elementos não existem na página

        const end = new Date();
        const start = new Date();
        start.setDate(end.getDate() - (days - 1));
        startEl.value = start.toISOString().slice(0, 10);
        endEl.value = end.toISOString().slice(0, 10);
    };

    const selected = (name) => $$(`input[name="${name}"]:checked`).map(i => i.value);
    const inSet = (val, set) => set.length ? set.includes(val) : true;
    const inDate = (d, s, e) => (!s || d >= s) && (!e || d <= e);

    const filterData = () => {
        const { start, end } = getRange();
        const chans = selected('canal');
        const devs = selected('disp');
        return DATA.filter(r => inDate(r.date, start, end) && inSet(r.canal, chans) && inSet(r.disp, devs));
    };

    const sum = (arr, k) => arr.reduce((a, b) => a + (b[k] || 0), 0);
    const groupBy = (arr, key) => arr.reduce((acc, it) => (acc[it[key]] = (acc[it[key]] || []).concat(it), acc), {});
    const byDay = (arr) => {
        const g = groupBy(arr, 'date');
        return Object.keys(g).sort().map(d => ({
            date: d,
            spend: sum(g[d], 'spend'),
            rev: sum(g[d], 'rev')
        }));
    };
    const byProduct = (arr) => {
        const g = groupBy(arr, 'product');
        const rows = Object.keys(g).map(p => ({ product: p, rev: sum(g[p], 'rev') }));
        rows.sort((a, b) => b.rev - a.rev);
        return rows.slice(0, 8);
    };
    const byCampaign = (arr) => {
        const g = groupBy(arr, 'campanha');
        return Object.keys(g).map(c => {
            const rows = g[c];
            const canal = rows[0].canal,
                disp = rows[0].disp,
                status = rows[0].status;
            const impr = sum(rows, 'impr'),
                clicks = sum(rows, 'clicks'),
                spend = sum(rows, 'spend');
            const conv = sum(rows, 'conv'),
                rev = sum(rows, 'rev');
            const ctr = clicks / Math.max(1, impr);
            const cvr = conv / Math.max(1, clicks);
            const cpc = spend / Math.max(1, clicks);
            const cac = spend / Math.max(1, conv);
            const roas = rev / Math.max(1, spend);
            return { campanha: c, canal, disp, impr, clicks, ctr, spend, cpc, conv, cvr, rev, roas, cac, status };
        });
    };

    /* -------- Gráficos (canvas puro) -------- */
    const lineChart = (canvas, seriesA, seriesB, labels, labelA = 'Receita', labelB = 'Invest.') => {
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const W = canvas.width = canvas.clientWidth * (window.devicePixelRatio || 1);
        const H = canvas.height; // já vem do atributo
        ctx.save();
        ctx.scale(window.devicePixelRatio || 1, window.devicePixelRatio || 1);

        const pad = { l: 42, r: 14, t: 20, b: 24 };
        const w = canvas.clientWidth - pad.l - pad.r;
        const h = (H / (window.devicePixelRatio || 1)) - pad.t - pad.b;

        const maxY = Math.max(...seriesA, ...seriesB) * 1.15 || 1;
        const X = i => pad.l + (w * i / (labels.length - 1));
        const Y = v => pad.t + h - (v / maxY) * h;

        // axes
        ctx.strokeStyle = 'rgba(255,255,255,.12)';
        ctx.lineWidth = 1;
        ctx.beginPath();
        ctx.moveTo(pad.l, pad.t);
        ctx.lineTo(pad.l, pad.t + h);
        ctx.lineTo(pad.l + w, pad.t + h);
        ctx.stroke();
        // y ticks
        ctx.fillStyle = getComputedStyle(document.body).getPropertyValue('--muted').trim() || '#9fb3d9';
        ctx.font = '12px Inter,system-ui';
        const ticks = 4;
        for (let i = 0; i <= ticks; i++) {
            const v = (maxY / ticks) * i;
            const y = Y(v);
            ctx.strokeStyle = 'rgba(255,255,255,.08)';
            ctx.beginPath();
            ctx.moveTo(pad.l, y);
            ctx.lineTo(pad.l + w, y);
            ctx.stroke();
            ctx.fillText(fmtMoney(v), 6, y + 4);
        }

        // lines
        const colA = '#20d3ff',
            colB = '#7c4dff';
        const drawLine = (vals, col) => {
            ctx.strokeStyle = col;
            ctx.lineWidth = 2;
            ctx.beginPath();
            vals.forEach((v, i) => i ? ctx.lineTo(X(i), Y(v)) : ctx.moveTo(X(i), Y(v)));
            ctx.stroke();
            // dots
            ctx.fillStyle = col;
            vals.forEach((v, i) => { ctx.beginPath();
                ctx.arc(X(i), Y(v), 2.2, 0, Math.PI * 2);
                ctx.fill(); });
        };
        drawLine(seriesA, colA);
        drawLine(seriesB, colB);

        // legend
        ctx.fillStyle = '#fff';
        ctx.globalAlpha = .9;
        ctx.fillRect(pad.l, 6, 10, 3);
        ctx.fillStyle = '#20d3ff';
        ctx.fillRect(pad.l, 6, 10, 3);
        ctx.fillStyle = getComputedStyle(document.body).getPropertyValue('--text').trim() || '#e9f0ff';
        ctx.fillText(labelA, pad.l + 16, 10);
        ctx.fillStyle = '#7c4dff';
        ctx.fillRect(pad.l + 90, 6, 10, 3);
        ctx.fillStyle = getComputedStyle(document.body).getPropertyValue('--text').trim() || '#e9f0ff';
        ctx.fillText(labelB, pad.l + 106, 10);

        ctx.restore();
    };

    const barChart = (canvas, labels, values) => {
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const DPR = window.devicePixelRatio || 1;
        const W = canvas.width = canvas.clientWidth * DPR;
        const H = canvas.height;
        ctx.save();
        ctx.scale(DPR, DPR);

        const pad = { l: 120, r: 16, t: 12, b: 24 };
        const w = canvas.clientWidth - pad.l - pad.r;
        const h = (H / DPR) - pad.t - pad.b;
        const maxV = Math.max(...values) * 1.1 || 1;
        const barH = h / values.length * 0.66;
        const Y = i => pad.t + i * (h / values.length) + (h / values.length - barH) / 2;
        const X = v => pad.l + (v / maxV) * w;

        ctx.font = '12px Inter,system-ui';
        ctx.fillStyle = getComputedStyle(document.body).getPropertyValue('--muted').trim() || '#9fb3d9';

        values.forEach((v, i) => {
            const y = Y(i);
            // grid
            ctx.strokeStyle = 'rgba(255,255,255,.08)';
            ctx.beginPath();
            ctx.moveTo(pad.l, y + barH + 4);
            ctx.lineTo(pad.l + w, y + barH + 4);
            ctx.stroke();
            // label
            ctx.fillText(labels[i], 10, y + barH * 0.72);
            // bar
            ctx.fillStyle = '#7c4dff';
            ctx.fillRect(pad.l, y, X(v) - pad.l, barH);
            // value
            ctx.fillStyle = getComputedStyle(document.body).getPropertyValue('--text').trim() || '#e9f0ff';
            ctx.fillText(fmtMoney(v), X(v) + 6, y + barH * 0.72);
            ctx.fillStyle = getComputedStyle(document.body).getPropertyValue('--muted').trim() || '#9fb3d9';
        });

        ctx.restore();
    };

    const hbarChartPct = (canvas, labels, values) => {
        // valores são percentuais (0..1)
        const vals = values.map(v => Math.max(0, v));
        const total = vals.reduce((a, b) => a + b, 0) || 1;
        const abs = vals.map(v => v / total);
        const vMoney = false;
        const ctx = canvas.getContext('2d');
        const DPR = window.devicePixelRatio || 1;
        const W = canvas.width = canvas.clientWidth * DPR;
        const H = canvas.height;
        ctx.save();
        ctx.scale(DPR, DPR);

        const pad = { l: 120, r: 16, t: 12, b: 24 };
        const w = canvas.clientWidth - pad.l - pad.r;
        const h = (H / DPR) - pad.t - pad.b;
        const barH = h / values.length * 0.66;
        const Y = i => pad.t + i * (h / values.length) + (h / values.length - barH) / 2;
        const X = v => pad.l + v * w;

        ctx.font = '12px Inter,system-ui';
        const text = getComputedStyle(document.body).getPropertyValue('--text').trim() || '#e9f0ff';
        const muted = getComputedStyle(document.body).getPropertyValue('--muted').trim() || '#9fb3d9';

        abs.forEach((v, i) => {
            const y = Y(i);
            // bg
            ctx.fillStyle = 'rgba(255,255,255,.08)';
            ctx.fillRect(pad.l, y, w, barH);
            // bar
            ctx.fillStyle = i === 0 ? '#20d3ff' : i === 1 ? '#7c4dff' : '#8bd3ff';
            ctx.fillRect(pad.l, y, X(v) - pad.l, barH);
            // labels
            ctx.fillStyle = muted;
            ctx.fillText(labels[i], 10, y + barH * 0.72);
            ctx.fillStyle = text;
            ctx.fillText(pct(v), X(v) + 6, y + barH * 0.72);
        });

        ctx.restore();
    };

    /* -------- Renderização -------- */
    let sortKey = 'roas',
        sortDir = -1;

    const render = () => {
        const rows = filterData();
        const revenue = sum(rows, 'rev');
        const spend = sum(rows, 'spend');
        const orders = sum(rows, 'conv');
        const clicks = sum(rows, 'clicks');
        const aov = orders ? revenue / orders : 0;
        const cvr = clicks ? (orders / clicks) : 0;
        const cac = orders ? (spend / orders) : 0;
        const roas = spend ? (revenue / spend) : 0;

        // KPIs
        $('#kpi-revenue').textContent = fmtMoney(revenue);
        $('#kpi-orders').textContent = orders.toLocaleString('pt-BR');
        $('#kpi-aov').textContent = fmtMoney(aov);
        $('#kpi-cvr').textContent = (cvr * 100).toFixed(2) + '%';
        $('#kpi-spend').textContent = fmtMoney(spend);
        $('#kpi-cac').textContent = isFinite(cac) ? fmtMoney(cac) : '—';
        $('#kpi-roas').textContent = isFinite(roas) ? roas.toFixed(1) + 'x' : '—';

        // Alertas
        const alerts = [];
        if (roas < 2.0 && spend > 0) alerts.push({ type: 'bad', msg: `ROAS ${roas.toFixed(1)}x abaixo da meta (≥ 2,0x)` });
        if (cac > 80 && isFinite(cac)) alerts.push({ type: 'warn', msg: `CAC alto: ${fmtMoney(cac)} (meta ≤ R$ 80,00)` });
        if ((cvr * 100) < 1.5) alerts.push({ type: 'warn', msg: `Conversão baixa: ${(cvr*100).toFixed(2)}% (meta ≥ 1,50%)` });
        const cpc = clicks ? (spend / clicks) : 0;
        if (cpc > 2.5) alerts.push({ type: 'warn', msg: `CPC elevado: ${fmtMoney(cpc)} (meta ≤ R$ 2,50)` });
        const al = $('#alerts-list');
        al.innerHTML = '';
        if (!alerts.length) { $('#alerts-empty').style.display = 'block'; } else {
            $('#alerts-empty').style.display = 'none';
            alerts.forEach(a => {
                const li = document.createElement('li');
                li.className = a.type === 'bad' ? 'alert-bad' : a.type === 'warn' ? 'alert-warn' : 'alert-ok';
                li.innerHTML = `<span>${a.msg}</span><span class="alert-pill">Alvo</span>`;
                al.appendChild(li);
            });
        }

        // Charts
        const daily = byDay(rows);
        lineChart($('#chart-timeseries'),
            daily.map(d => d.rev), daily.map(d => d.spend), daily.map(d => d.date),
            'Receita', 'Invest.'
        );
        const tops = byProduct(rows);
        barChart($('#chart-topprod'), tops.map(t => t.product), tops.map(t => t.rev));

        const devGroup = groupBy(rows, 'disp');
        const devLabels = ['desktop', 'mobile', 'tablet'];
        const devVals = devLabels.map(d => sum(devGroup[d] || [], 'rev'));
        hbarChartPct($('#chart-devices'), ['Desktop', 'Mobile', 'Tablet'], devVals);

        // Tabela por campanha
        const rowsCamp = byCampaign(rows).sort((a, b) => (a[sortKey] - b[sortKey]) * sortDir);
        const tbody = $('#tbl-campanhas tbody');
        tbody.innerHTML = '';
        const fmtPct = v => (v * 100).toFixed(2) + '%';
        const fmtMaybe = (v, is$) => isFinite(v) ? (is$ ? fmtMoney(v) : v.toLocaleString('pt-BR')) : '—';
        rowsCamp.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
        <td>${r.campanha}</td>
        <td>${r.canal}</td>
        <td>${r.disp}</td>
        <td class="num">${fmtMaybe(r.impr)}</td>
        <td class="num">${fmtMaybe(r.clicks)}</td>
        <td class="num">${fmtPct(r.ctr)}</td>
        <td class="num">${fmtMoney(r.spend)}</td>
        <td class="num">${fmtMoney(r.cpc)}</td>
        <td class="num">${fmtMaybe(r.conv)}</td>
        <td class="num">${fmtPct(r.cvr)}</td>
        <td class="num">${fmtMoney(r.rev)}</td>
        <td class="num">${isFinite(r.roas)? r.roas.toFixed(1)+'x':'—'}</td>
        <td class="num">${isFinite(r.cac)? fmtMoney(r.cac):'—'}</td>
        <td><span class="status-pill ${r.status==='on'?'status-on':r.status==='paused'?'status-paused':'status-off'}">
          ${r.status==='on'?'Ativa': r.status==='paused'?'Pausada':'Off'}
        </span></td>
      `;
            tbody.appendChild(tr);
        });
    };

    // Ordenação por cabeçalho
    $$('#tbl-campanhas thead th').forEach(th => {
        th.addEventListener('click', () => {
            const k = th.getAttribute('data-k');
            if (!k) return;
            sortDir = (sortKey === k) ? -sortDir : -1;
            sortKey = k;
            render();
        });
    });

    // Export CSV
    const toCSV = (rows) => {
        const headers = ['Campanha', 'Canal', 'Disp', 'Impr', 'Cliques', '%CTR', 'Invest', 'CPC', 'Conv', '%CVR', 'Receita', 'ROAS', 'CAC', 'Status'];
        const map = r => [
            r.campanha, r.canal, r.disp, r.impr, r.clicks, (r.ctr * 100).toFixed(2) + '%',
            r.spend.toFixed(2), r.cpc.toFixed(2), r.conv, (r.cvr * 100).toFixed(2) + '%',
            r.rev.toFixed(2), isFinite(r.roas) ? r.roas.toFixed(2) : '', isFinite(r.cac) ? r.cac.toFixed(2) : '', r.status
        ];
        const arr = byCampaign(filterData());
        const lines = [headers.join(','), ...arr.map(map).map(a => a.join(','))];
        return lines.join('\n');
    };
    $('#btn-export') ? .addEventListener('click', () => {
        const csv = toCSV();
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `relatorio_ecom_${todayISO()}.csv`;
        document.body.appendChild(a);
        a.click();
        a.remove();
    });

    // Aplicar / Reset
    $('#btn-aplicar') ? .addEventListener('click', render);
    $('#btn-reset') ? .addEventListener('click', () => {
        setDefaultRange(30);
        $$('input[name="canal"]').forEach(i => i.checked = true);
        $$('input[name="disp"]').forEach(i => i.checked = true);
        render();
    });

    // Inicializa período padrão (últimos 30 dias) apenas se os elementos existirem
    if ($('#flt-start') && $('#flt-end')) {
        setDefaultRange(30);
        render();
    }

    // Redesenha gráficos no resize (debounce)
    let rto;
    window.addEventListener('resize', () => {
        clearTimeout(rto);
        rto = setTimeout(render, 200);
    });
})();
