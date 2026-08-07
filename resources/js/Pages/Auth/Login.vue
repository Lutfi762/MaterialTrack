<template>
    <Head><title>Login - MaterialTrack</title></Head>
    <div class="login-page">
        <div class="login-visual">
            <!-- Animated background particles -->
            <div class="particles">
                <span v-for="n in 20" :key="n" class="particle" :style="particleStyle(n)"></span>
            </div>

            <!-- Floating geometric shapes -->
            <div class="floating-shape shape-1"></div>
            <div class="floating-shape shape-2"></div>
            <div class="floating-shape shape-3"></div>

            <div class="login-visual-content" :class="{ 'visible': mounted }">
                <div class="brand-logo">
                    <div class="brand-icon-wrap">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="white" viewBox="0 0 16 16">
                            <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zm-7.5 10.522V6.84L1 4.24v7.922l6.5 2.6z"/>
                        </svg>
                    </div>
                    <span>MaterialTrack</span>
                </div>

                <h2 class="visual-title">
                    Kelola Stok Gudang<br>
                    <span class="gradient-text">dengan Lebih Cerdas</span>
                </h2>
                <p class="visual-sub">Pantau material masuk, keluar, dan stok kritis secara real-time dalam satu sistem terintegrasi.</p>

                <div class="feature-list">
                    <div class="feature-item" v-for="(f, i) in features" :key="i" :style="{ transitionDelay: (i * 0.1 + 0.3) + 's' }">
                        <div class="feature-icon"><i :class="f.icon"></i></div>
                        <span>{{ f.text }}</span>
                    </div>
                </div>

                <div class="stats-row">
                    <div class="stat-mini">
                        <div class="stat-mini-value"><span class="counter-num">{{ animatedStats.uptime }}</span>%</div>
                        <div class="stat-mini-label">Uptime</div>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-mini">
                        <div class="stat-mini-value"><span class="counter-num">{{ animatedStats.speed }}</span>ms</div>
                        <div class="stat-mini-label">Response</div>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="stat-mini">
                        <div class="stat-mini-value"><span class="counter-num">{{ animatedStats.secure }}</span>%</div>
                        <div class="stat-mini-label">Secure</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="login-form-side">
            <div class="login-form-box" :class="{ 'visible': mounted }">
                <div class="mobile-brand d-lg-none">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#3b82f6" viewBox="0 0 16 16">
                        <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zm-7.5 10.522V6.84L1 4.24v7.922l6.5 2.6z"/>
                    </svg>
                    <span>MaterialTrack</span>
                </div>

                <div class="form-icon-badge">
                    <i class="fa fa-shield-halved"></i>
                </div>

                <h3 class="form-title">Selamat Datang Kembali</h3>
                <p class="form-sub">Masuk ke akun Anda untuk melanjutkan</p>

                <form @submit.prevent="submit">
                    <div class="input-modern mb-3" :class="{ 'focused': focusEmail, 'has-value': form.email }">
                        <label>Alamat Email</label>
                        <div class="input-wrap">
                            <i class="fa fa-envelope"></i>
                            <input
                                v-model="form.email"
                                type="email"
                                placeholder="nama@materialtrack.com"
                                :class="{ 'is-invalid': form.errors.email }"
                                @focus="focusEmail = true"
                                @blur="focusEmail = false"
                            >
                        </div>
                        <div v-if="form.errors.email" class="invalid-feedback d-block">{{ form.errors.email }}</div>
                    </div>

                    <div class="input-modern mb-3" :class="{ 'focused': focusPassword, 'has-value': form.password }">
                        <label>Password</label>
                        <div class="input-wrap">
                            <i class="fa fa-lock"></i>
                            <input
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                placeholder="••••••••"
                                :class="{ 'is-invalid': form.errors.password }"
                                @focus="focusPassword = true"
                                @blur="focusPassword = false"
                            >
                            <button type="button" class="toggle-password" @click="showPassword = !showPassword">
                                <i class="fa" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        <div v-if="form.errors.password" class="invalid-feedback d-block">{{ form.errors.password }}</div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <label class="remember-check">
                            <input type="checkbox" v-model="form.remember">
                            <span class="check-box"></span>
                            <span>Ingat Saya</span>
                        </label>
                        <Link href="/forgot-password" class="forgot-link">Lupa Password?</Link>
                    </div>

                    <button type="submit" class="btn-login" :disabled="form.processing">
                        <span class="btn-content" v-if="!form.processing">
                            Masuk <i class="fa fa-arrow-right ms-2"></i>
                        </span>
                        <span class="btn-content" v-else>
                            <i class="fa fa-spinner fa-spin me-2"></i>Memproses...
                        </span>
                        <span class="btn-shine"></span>
                    </button>
                </form>

                <p class="footer-note">© 2026 MaterialTrack. Sistem Manajemen Stok Gudang.</p>
            </div>
        </div>
    </div>
</template>

<script>
import { Head, Link, useForm } from '@inertiajs/vue3';

export default {
    components: { Head, Link },
    data() {
        return {
            showPassword: false,
            focusEmail: false,
            focusPassword: false,
            mounted: false,
            animatedStats: { uptime: 0, speed: 0, secure: 0 },
            features: [
                { icon: 'fa fa-chart-line', text: 'Dashboard real-time & laporan otomatis' },
                { icon: 'fa fa-users', text: 'Manajemen akses per departemen' },
                { icon: 'fa fa-file-export', text: 'Export PDF & Excel otomatis' },
            ],
        };
    },
    mounted() {
        setTimeout(() => { this.mounted = true; }, 100);
        this.animateStats();
    },
    methods: {
        particleStyle(n) {
            const size = 2 + (n % 5);
            const left = (n * 37) % 100;
            const duration = 12 + (n % 10);
            const delay = (n % 8) * 0.7;
            return {
                width: size + 'px',
                height: size + 'px',
                left: left + '%',
                animationDuration: duration + 's',
                animationDelay: delay + 's',
            };
        },
        animateStats() {
            const targets = { uptime: 99, speed: 120, secure: 100 };
            const duration = 1200;
            const startTime = performance.now();

            const step = (now) => {
                const progress = Math.min((now - startTime) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                this.animatedStats.uptime = Math.round(targets.uptime * eased);
                this.animatedStats.speed = Math.round(targets.speed * eased);
                this.animatedStats.secure = Math.round(targets.secure * eased);
                if (progress < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        },
    },
    setup() {
        const form = useForm({
            email: '',
            password: '',
            remember: false,
        });

        const submit = () => {
            form.post('/login', {
                onFinish: () => form.reset('password'),
            });
        };

        return { form, submit };
    },
}
</script>

<style scoped>
* { box-sizing: border-box; }

.login-page {
    min-height: 100vh;
    display: flex;
    overflow: hidden;
}

/* ===== LEFT VISUAL PANEL ===== */
.login-visual {
    flex: 1.1;
    background: linear-gradient(145deg, #1a2942 0%, #0f1f35 60%, #0a1628 100%);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    padding: 60px;
}

/* Floating particles animation */
.particles {
    position: absolute;
    inset: 0;
    overflow: hidden;
}
.particle {
    position: absolute;
    bottom: -10px;
    background: rgba(96, 165, 250, 0.4);
    border-radius: 50%;
    animation: floatUp linear infinite;
}
@keyframes floatUp {
    0% { transform: translateY(0) translateX(0); opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 0.6; }
    100% { transform: translateY(-100vh) translateX(30px); opacity: 0; }
}

/* Floating geometric shapes */
.floating-shape {
    position: absolute;
    border-radius: 30%;
    background: linear-gradient(135deg, rgba(59,130,246,0.15), rgba(96,165,250,0.05));
    border: 1px solid rgba(96,165,250,0.15);
}
.shape-1 {
    width: 200px; height: 200px;
    top: -60px; right: -60px;
    animation: rotateFloat 20s ease-in-out infinite;
}
.shape-2 {
    width: 120px; height: 120px;
    bottom: 60px; right: 100px;
    border-radius: 50%;
    animation: rotateFloat 15s ease-in-out infinite reverse;
}
.shape-3 {
    width: 80px; height: 80px;
    top: 40%; right: 20px;
    animation: rotateFloat 18s ease-in-out infinite;
}
@keyframes rotateFloat {
    0%, 100% { transform: translate(0, 0) rotate(0deg); }
    33% { transform: translate(-15px, 20px) rotate(10deg); }
    66% { transform: translate(10px, -15px) rotate(-8deg); }
}

.login-visual-content {
    position: relative;
    z-index: 2;
    color: white;
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.7s ease, transform 0.7s ease;
}
.login-visual-content.visible {
    opacity: 1;
    transform: translateY(0);
}

.brand-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 48px;
}
.brand-icon-wrap {
    width: 42px; height: 42px;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 8px 20px rgba(37,99,235,0.4);
    animation: pulseGlow 3s ease-in-out infinite;
}
@keyframes pulseGlow {
    0%, 100% { box-shadow: 0 8px 20px rgba(37,99,235,0.4); }
    50% { box-shadow: 0 8px 30px rgba(37,99,235,0.7); }
}

.visual-title {
    font-size: 34px;
    font-weight: 700;
    line-height: 1.3;
    margin-bottom: 16px;
}
.gradient-text {
    background: linear-gradient(90deg, #60a5fa, #93c5fd, #60a5fa);
    background-size: 200% auto;
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: shimmer 3s linear infinite;
}
@keyframes shimmer {
    to { background-position: 200% center; }
}

.visual-sub {
    font-size: 14px;
    color: rgba(255,255,255,0.6);
    max-width: 380px;
    margin-bottom: 40px;
    line-height: 1.6;
}

.feature-list { display: flex; flex-direction: column; gap: 16px; margin-bottom: 40px; }
.feature-item {
    display: flex; align-items: center; gap: 14px;
    font-size: 13px; color: rgba(255,255,255,0.85);
    opacity: 0;
    transform: translateX(-15px);
    transition: opacity 0.5s ease, transform 0.5s ease;
}
.login-visual-content.visible .feature-item {
    opacity: 1;
    transform: translateX(0);
}
.feature-icon {
    width: 38px; height: 38px;
    background: rgba(59,130,246,0.2);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #60a5fa;
    flex-shrink: 0;
    transition: transform 0.2s ease;
}
.feature-item:hover .feature-icon {
    transform: scale(1.1) rotate(-5deg);
    background: rgba(59,130,246,0.35);
}

.stats-row {
    display: flex;
    align-items: center;
    gap: 20px;
    padding-top: 24px;
    border-top: 1px solid rgba(255,255,255,0.1);
}
.stat-mini-value {
    font-size: 22px;
    font-weight: 700;
    color: #60a5fa;
}
.stat-mini-label {
    font-size: 10px;
    color: rgba(255,255,255,0.5);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 2px;
}
.stat-divider {
    width: 1px; height: 30px;
    background: rgba(255,255,255,0.15);
}

/* ===== RIGHT FORM PANEL ===== */
.login-form-side {
    flex: 1;
    background: #fafbfc;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px;
}

.login-form-box {
    width: 100%;
    max-width: 380px;
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.7s ease 0.15s, transform 0.7s ease 0.15s;
}
.login-form-box.visible {
    opacity: 1;
    transform: translateY(0);
}

.mobile-brand {
    display: flex; align-items: center; gap: 10px;
    font-size: 18px; font-weight: 700; color: #1a2942;
    margin-bottom: 24px;
}

.form-icon-badge {
    width: 52px; height: 52px;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    color: #2563eb;
    font-size: 20px;
    margin-bottom: 20px;
    animation: bounceIn 0.6s ease 0.3s both;
}
@keyframes bounceIn {
    0% { transform: scale(0); opacity: 0; }
    60% { transform: scale(1.15); }
    100% { transform: scale(1); opacity: 1; }
}

.form-title { font-size: 24px; font-weight: 700; color: #1a2942; margin-bottom: 4px; }
.form-sub { font-size: 13px; color: #94a3b8; margin-bottom: 32px; }

.input-modern { position: relative; }
.input-modern label {
    font-size: 12px; font-weight: 600; color: #475569;
    margin-bottom: 6px; display: block;
    transition: color 0.2s ease;
}
.input-modern.focused label { color: #2563eb; }

.input-wrap { position: relative; display: flex; align-items: center; }
.input-wrap i.fa-envelope, .input-wrap i.fa-lock {
    position: absolute; left: 14px; color: #94a3b8; font-size: 14px;
    transition: color 0.2s ease;
}
.input-modern.focused .input-wrap i.fa-envelope,
.input-modern.focused .input-wrap i.fa-lock { color: #2563eb; }

.input-wrap input {
    width: 100%;
    height: 48px;
    border-radius: 10px;
    border: 1.5px solid #e2e8f0;
    padding: 0 44px;
    font-size: 14px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.input-wrap input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 4px rgba(59,130,246,0.1);
}
.toggle-password {
    position: absolute; right: 12px;
    background: none; border: none; color: #94a3b8; cursor: pointer;
    transition: color 0.2s ease;
}
.toggle-password:hover { color: #3b82f6; }

.remember-check {
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; color: #64748b; cursor: pointer;
    user-select: none;
}
.remember-check input { display: none; }
.check-box {
    width: 16px; height: 16px;
    border: 1.5px solid #cbd5e1;
    border-radius: 4px;
    display: inline-flex; align-items: center; justify-content: center;
    transition: all 0.2s ease;
}
.remember-check input:checked + .check-box {
    background: #3b82f6;
    border-color: #3b82f6;
}
.remember-check input:checked + .check-box::after {
    content: '✓';
    color: white;
    font-size: 11px;
    font-weight: bold;
}

.forgot-link { font-size: 13px; color: #3b82f6; text-decoration: none; font-weight: 500; }
.forgot-link:hover { text-decoration: underline; }

.btn-login {
    width: 100%;
    height: 48px;
    border-radius: 10px;
    border: none;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    font-weight: 600;
    font-size: 14px;
    box-shadow: 0 6px 16px rgba(37,99,235,0.3);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    position: relative;
    overflow: hidden;
}
.btn-login:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(37,99,235,0.4);
}
.btn-login:active:not(:disabled) { transform: translateY(0); }
.btn-login:disabled { opacity: 0.75; cursor: not-allowed; }

.btn-content { position: relative; z-index: 1; }
.btn-shine {
    position: absolute;
    top: 0; left: -100%;
    width: 60%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transform: skewX(-20deg);
    transition: left 0.6s ease;
}
.btn-login:hover .btn-shine { left: 130%; }

.footer-note { text-align: center; font-size: 11px; color: #cbd5e1; margin-top: 32px; }

@media (max-width: 991px) {
    .login-visual { display: none; }
    .login-form-side { padding: 24px; }
}
</style>