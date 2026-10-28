<template>
    <Head><title>Masuk - MaterialTrack</title></Head>

    <div class="login-page">
        <section class="login-story" aria-label="MaterialTrack overview">
            <div class="story-bg" aria-hidden="true">
                <div class="glow glow-large"></div>
                <div class="glow glow-small"></div>
                <div class="light-sweep"></div>
            </div>

            <div class="story-content">
                <div class="story-icons" aria-hidden="true">
                    <i class="fa fa-box"></i>
                    <i class="fa fa-barcode"></i>
                    <i class="fa fa-location-dot"></i>
                </div>

                <h1>
                    {{ $t('auth.hero_title') }}
                    <span>{{ $t('auth.hero_subtitle') }}</span>
                </h1>

                <p class="story-copy">
                    {{ $t('auth.hero_description') }}
                </p>

                <ul class="feature-list">
                    <li
                        v-for="(feature, index) in features"
                        :key="feature.title"
                        class="feature-item"
                        :style="{ transitionDelay: `${240 + index * 100}ms` }"
                    >
                        <div class="feature-icon">
                            <i :class="feature.icon"></i>
                        </div>
                        <div>
                            <h2>{{ $t(feature.titleKey) }}</h2>
                            <p>{{ $t(feature.descKey) }}</p>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="story-stats">
                <div>
                    <strong>150K+</strong>
                    <span>{{ $t('auth.stat_items') }}</span>
                </div>
                <div>
                    <strong>500K+</strong>
                    <span>{{ $t('auth.stat_transactions') }}</span>
                </div>
                <div>
                    <strong>99%</strong>
                    <span>{{ $t('auth.stat_uptime') }}</span>
                </div>
            </div>
        </section>

        <main class="login-main">
            <div class="login-card">
                <div class="security-badge" aria-hidden="true">
                    <i class="fa fa-shield-halved"></i>
                </div>

                <header class="form-heading">
                    <h2>{{ $t('auth.welcome_back') }}</h2>
                    <p>{{ $t('auth.login_subtitle') }}</p>
                </header>

                <form class="login-form" @submit.prevent="submit" novalidate>
                    <div class="field-group">
                        <label for="email">{{ $t('auth.email') }}</label>
                        <div class="field-shell" :class="{ 'has-error': form.errors.email }">
                            <i class="fa fa-envelope field-icon" aria-hidden="true"></i>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                placeholder="nama@perusahaan.com"
                                autocomplete="email"
                                required
                            >
                        </div>
                        <p v-if="form.errors.email" class="field-error">
                            <i class="fa fa-circle-exclamation" aria-hidden="true"></i>
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div class="field-group">
                        <label for="password">{{ $t('auth.password') }}</label>
                        <div class="field-shell" :class="{ 'has-error': form.errors.password }">
                            <i class="fa fa-lock field-icon" aria-hidden="true"></i>
                            <input
                                id="password"
                                v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                :placeholder="$t('auth.password_placeholder')"
                                autocomplete="current-password"
                                required
                            >
                            <button
                                type="button"
                                class="password-toggle"
                                :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                                @click="showPassword = !showPassword"
                            >
                                <i class="fa" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="field-error">
                            <i class="fa fa-circle-exclamation" aria-hidden="true"></i>
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div class="form-options">
                        <label class="remember-option" for="remember-me">
                            <input id="remember-me" v-model="form.remember" type="checkbox">
                            <span class="check-visual">
                                <i class="fa fa-check" aria-hidden="true"></i>
                            </span>
                            <span>{{ $t('auth.remember') }}</span>
                        </label>

                        <Link href="/forgot-password" class="forgot-link">
                            {{ $t('auth.forgot_password') }}
                        </Link>
                    </div>

                    <button class="submit-button" type="submit" :disabled="form.processing">
                        <span v-if="form.processing" class="spinner">
                            <i class="fa fa-circle-notch fa-spin" aria-hidden="true"></i>
                        </span>
                        <span>{{ form.processing ? $t('auth.verifying') : $t('auth.sign_in_button') }}</span>
                        <i v-if="!form.processing" class="fa fa-arrow-right submit-arrow" aria-hidden="true"></i>
                    </button>

                    <div class="divider">
                        <span>{{ $t('auth.or') }}</span>
                    </div>

                    <a href="/auth/google/redirect" class="google-button">
                        <i class="fa-brands fa-google" aria-hidden="true"></i>
                        <span>{{ $t('auth.continue_with_google') }}</span>
                    </a>

                    <p class="access-note">
                        <i class="fa fa-circle-info" aria-hidden="true"></i>
                        {{ $t('auth.no_access') }}
                    </p>
                </form>

                <div class="brand-lockup">
                    <img :src="logoUrl" alt="MaterialTrack" />
                </div>
            </div>

            <footer class="login-footer">
                MaterialTrack &copy; 2026 &bull; {{ $t('auth.footer_text') }}
            </footer>
        </main>
    </div>
</template>

<script>
import { Head, Link, useForm } from '@inertiajs/vue3';

export default {
    components: { Head, Link },

    data() {
        return {
            logoUrl: '/images/logo.png',
            showPassword: false,
            features: [
                {
                    icon: 'fa fa-cube',
                    titleKey: 'auth.feature_1_title',
                    descKey: 'auth.feature_1_desc',
                },
                {
                    icon: 'fa fa-chart-line',
                    titleKey: 'auth.feature_2_title',
                    descKey: 'auth.feature_2_desc',
                },
                {
                    icon: 'fa fa-users-gear',
                    titleKey: 'auth.feature_3_title',
                    descKey: 'auth.feature_3_desc',
                },
            ],
        };
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
};
</script>

<style scoped>
.login-page {
    --brand-navy: #0f172a;
    --brand-primary: #7c3aed;
    --brand-primary-dark: #6d28d9;
    --brand-bg: #f8fafc;
    --success: #16a34a;
    --gray-900: #0f172a;
    --gray-700: #334155;
    --gray-600: #475569;
    --gray-500: #64748b;
    --gray-400: #94a3b8;
    --gray-300: #cbd5e1;
    --gray-200: #e2e8f0;
    --gray-100: #f1f5f9;

    min-height: 100vh;
    display: flex;
    background: white;
    color: var(--gray-900);
    font-family: Inter, 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    overflow: hidden;
}

.login-story {
    width: 50%;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
    padding: 64px;
    background: var(--brand-navy);
    color: white;
}

.story-bg {
    position: absolute;
    inset: 0;
    pointer-events: none;
}

.glow {
    position: absolute;
    border-radius: 999px;
    filter: blur(64px);
    background: var(--brand-primary);
    mix-blend-mode: screen;
}

.glow-large {
    width: 384px;
    height: 384px;
    top: 22%;
    left: 20%;
    opacity: 0.28;
    animation: softPulse 5s ease-in-out infinite;
}

.glow-small {
    width: 256px;
    height: 256px;
    right: 15%;
    bottom: 22%;
    opacity: 0.18;
}

.light-sweep {
    position: absolute;
    width: 200%;
    height: 128px;
    top: -38%;
    left: 0;
    transform: rotate(-45deg);
    transform-origin: left;
    background: linear-gradient(90deg, transparent, rgba(124, 58, 237, 0.16), transparent);
}

.story-content,
.story-stats {
    position: relative;
    z-index: 1;
}

.story-content {
    max-width: 540px;
    margin-top: 32px;
    animation: enterUp 650ms ease-out both;
}

.story-icons {
    display: flex;
    gap: 18px;
    margin-bottom: 48px;
    color: rgba(203, 213, 225, 0.55);
    font-size: 30px;
}

.story-content h1 {
    margin: 0 0 18px;
    font-size: clamp(36px, 4vw, 52px);
    line-height: 1.08;
    font-weight: 750;
    letter-spacing: 0;
}

.story-content h1 span {
    display: block;
    color: #c4b5fd;
}

.story-copy {
    margin: 0 0 48px;
    color: #cbd5e1;
    font-size: 18px;
    line-height: 1.7;
}

.feature-list {
    display: flex;
    flex-direction: column;
    gap: 24px;
    margin: 0;
    padding: 0;
    list-style: none;
}

.feature-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    opacity: 0;
    transform: translateY(14px);
    animation: enterUp 560ms ease-out forwards;
}

.feature-icon {
    width: 40px;
    height: 40px;
    flex: 0 0 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 4px;
    border-radius: 10px;
    border: 1px solid rgba(196, 181, 253, 0.25);
    background: rgba(124, 58, 237, 0.18);
    color: #c4b5fd;
    font-size: 18px;
    transition: transform 180ms ease-out, background 180ms ease-out;
}

.feature-item:hover .feature-icon {
    transform: translateY(-2px);
    background: rgba(124, 58, 237, 0.28);
}

.feature-item h2 {
    margin: 0;
    color: white;
    font-size: 16px;
    font-weight: 650;
}

.feature-item p {
    margin: 5px 0 0;
    color: #cbd5e1;
    font-size: 14px;
    line-height: 1.5;
}

.story-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    padding-top: 32px;
    margin-top: 48px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    animation: enterUp 700ms ease-out 320ms both;
}

.story-stats strong {
    display: block;
    color: white;
    font-size: 26px;
    line-height: 1;
}

.story-stats span {
    display: block;
    margin-top: 8px;
    color: #cbd5e1;
    font-size: 13px;
}

.login-main {
    width: 50%;
    min-height: 100vh;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 72px 80px;
    background: white;
}

.login-card {
    width: 100%;
    max-width: 430px;
    animation: enterUp 650ms ease-out 120ms both;
}

.security-badge {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 32px;
    border-radius: 16px;
    background: #f5f3ff;
    color: var(--brand-primary);
    font-size: 22px;
}

.form-heading {
    margin-bottom: 40px;
}

.form-heading h2 {
    margin: 0 0 8px;
    color: var(--gray-900);
    font-size: 30px;
    line-height: 1.2;
    font-weight: 750;
    letter-spacing: 0;
}

.form-heading p {
    margin: 0;
    color: var(--gray-500);
    font-size: 15px;
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: 22px;
}

.field-group label {
    display: block;
    margin-bottom: 7px;
    color: var(--gray-700);
    font-size: 14px;
    font-weight: 600;
}

.field-shell {
    position: relative;
    display: flex;
    align-items: center;
}

.field-icon {
    position: absolute;
    left: 13px;
    color: var(--gray-400);
    font-size: 16px;
    pointer-events: none;
    transition: color 180ms ease-out;
}

.field-shell input {
    width: 100%;
    height: 44px;
    padding: 0 42px 0 40px;
    border: 1px solid var(--gray-300);
    border-radius: 8px;
    background: white;
    color: var(--gray-900);
    font-size: 14px;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
    transition: border-color 180ms ease-out, box-shadow 180ms ease-out;
}

.field-shell input::placeholder {
    color: var(--gray-400);
}

.field-shell input:focus {
    outline: none;
    border-color: var(--brand-primary);
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.12);
}

.field-shell:focus-within .field-icon {
    color: var(--brand-primary);
}

.field-shell.has-error input {
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.08);
}

.password-toggle {
    position: absolute;
    right: 0;
    inset-block: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    border: 0;
    background: transparent;
    color: var(--gray-400);
    cursor: pointer;
    transition: color 180ms ease-out;
}

.password-toggle:hover {
    color: var(--gray-600);
}

.field-error {
    display: flex;
    align-items: center;
    gap: 7px;
    margin: 8px 0 0;
    color: #dc2626;
    font-size: 12px;
}

.form-options {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
}

.remember-option {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--gray-600);
    font-size: 14px;
    cursor: pointer;
    user-select: none;
}

.remember-option input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.check-visual {
    width: 16px;
    height: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--gray-300);
    border-radius: 4px;
    background: white;
    color: white;
    font-size: 10px;
    transition: border-color 180ms ease-out, background 180ms ease-out;
}

.remember-option input:checked + .check-visual {
    border-color: var(--brand-primary);
    background: var(--brand-primary);
}

.forgot-link {
    color: var(--brand-primary);
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: color 180ms ease-out;
}

.forgot-link:hover {
    color: var(--brand-primary-dark);
    text-decoration: none;
}

.submit-button {
    width: 100%;
    height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    border: 0;
    border-radius: 8px;
    background: var(--brand-primary);
    color: white;
    font-size: 14px;
    font-weight: 650;
    cursor: pointer;
    box-shadow: 0 8px 18px rgba(124, 58, 237, 0.24);
    transition: background 180ms ease-out, transform 180ms ease-out, box-shadow 180ms ease-out;
}

.submit-button:hover:not(:disabled) {
    background: var(--brand-primary-dark);
    transform: translateY(-1px);
    box-shadow: 0 12px 24px rgba(124, 58, 237, 0.28);
}

.submit-button:disabled {
    cursor: not-allowed;
    opacity: 0.72;
}

.submit-arrow {
    transition: transform 180ms ease-out;
}

.submit-button:hover .submit-arrow {
    transform: translateX(3px);
}

.google-button {
    width: 100%;
    height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    border: 1px solid var(--gray-200);
    border-radius: 8px;
    background: white;
    color: var(--gray-700);
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: background 180ms ease-out, border-color 180ms ease-out;
}

.google-button:hover {
    background: var(--brand-bg);
    border-color: var(--gray-300);
    text-decoration: none;
}

.google-button i {
    color: #ea4335;
}

.divider {
    position: relative;
    display: flex;
    justify-content: center;
    padding: 10px 0;
}

.divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    border-top: 1px solid var(--gray-200);
}

.divider span {
    position: relative;
    padding: 0 10px;
    background: white;
    color: var(--gray-500);
    font-size: 14px;
}

.access-note {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    margin: 0;
    color: var(--gray-500);
    font-size: 14px;
    text-align: center;
}

.access-note i {
    color: var(--gray-400);
}

.brand-lockup {
    display: flex;
    justify-content: center;
    margin-top: 56px;
}

.brand-lockup img {
    width: min(220px, 70%);
    height: auto;
    object-fit: contain;
}

.login-footer {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 24px;
    color: var(--gray-400);
    font-size: 12px;
    text-align: center;
}

@keyframes enterUp {
    from {
        opacity: 0;
        transform: translateY(18px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes softPulse {
    0%,
    100% {
        transform: scale(1);
        opacity: 0.24;
    }
    50% {
        transform: scale(1.08);
        opacity: 0.34;
    }
}

@media (max-width: 1023px) {
    .login-page {
        display: block;
        min-height: 100vh;
        background: var(--brand-bg);
    }

    .login-story {
        display: none;
    }

    .login-main {
        width: 100%;
        min-height: 100vh;
        padding: 48px 24px 72px;
        background: var(--brand-bg);
    }

    .login-card {
        max-width: 420px;
        padding: 32px;
        border: 1px solid var(--gray-200);
        border-radius: 12px;
        background: white;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
    }

    .security-badge {
        margin-inline: auto;
    }

    .form-heading {
        text-align: center;
    }
}

@media (max-width: 520px) {
    .login-main {
        align-items: flex-start;
        padding: 24px 16px 72px;
    }

    .login-card {
        padding: 24px;
    }

    .form-options {
        align-items: flex-start;
        flex-direction: column;
        gap: 10px;
    }

    .form-heading h2 {
        font-size: 26px;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 1ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 1ms !important;
        scroll-behavior: auto !important;
    }
}
</style>
