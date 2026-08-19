<template>
    <div class="language-switcher">
        <button
            class="lang-btn"
            @click.stop="toggleDropdown"
            :aria-label="$t('header.language')"
            type="button"
        >
            <i class="fa fa-globe"></i>
            <span class="lang-current">{{ currentLocale.toUpperCase() }}</span>
            <i class="fa fa-chevron-down lang-arrow" :class="{ rotate: showDropdown }"></i>
        </button>

        <div v-if="showDropdown" class="lang-dropdown" @click.stop>
            <button
                v-for="localeOption in locales"
                :key="localeOption.code"
                class="lang-option"
                :class="{ active: currentLocale === localeOption.code }"
                @click="switchLanguage(localeOption.code)"
                type="button"
            >
                <span class="lang-code">{{ localeOption.code.toUpperCase() }}</span>
                <span class="lang-name">{{ localeOption.name }}</span>
                <i v-if="currentLocale === localeOption.code" class="fa fa-check lang-check"></i>
            </button>
        </div>
    </div>
</template>

<script>
import { usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

export default {
    setup() {
        const { locale } = useI18n();
        const page = usePage();

        return {
            currentLocale: locale,
            page,
        };
    },

    data() {
        return {
            showDropdown: false,
            locales: [
                { code: 'id', name: 'Bahasa Indonesia' },
                { code: 'en', name: 'English' },
            ],
        };
    },

    mounted() {
        const savedLocale = localStorage.getItem('locale');
        const userLocale = this.page.props.auth?.user?.locale;
        const initialLocale = savedLocale || userLocale;

        if (this.locales.some((localeOption) => localeOption.code === initialLocale)) {
            this.currentLocale = initialLocale;
            localStorage.setItem('locale', initialLocale);
        }

        document.addEventListener('click', this.closeDropdown);
    },

    beforeUnmount() {
        document.removeEventListener('click', this.closeDropdown);
    },

    methods: {
        toggleDropdown() {
            this.showDropdown = !this.showDropdown;
        },

        closeDropdown() {
            this.showDropdown = false;
        },

        async switchLanguage(locale) {
            if (!this.locales.some((localeOption) => localeOption.code === locale)) {
                return;
            }

            this.currentLocale = locale;
            localStorage.setItem('locale', locale);
            this.showDropdown = false;

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!token) {
                return;
            }

            try {
                await fetch('/api/user/locale', {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ locale }),
                });
            } catch (error) {
                console.error('Failed to save locale preference:', error);
            }
        },
    },
};
</script>

<style scoped>
.language-switcher {
    position: relative;
}

.lang-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: white;
    color: #475569;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: 'Inter', sans-serif;
}

.lang-btn:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}

.lang-current {
    font-variant-numeric: tabular-nums;
    letter-spacing: 0;
}

.lang-arrow {
    font-size: 10px;
    transition: transform 0.2s ease;
}

.lang-arrow.rotate {
    transform: rotate(180deg);
}

.lang-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    min-width: 200px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.1);
    padding: 6px;
    z-index: 1000;
    animation: fadeIn 0.15s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.lang-option {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    padding: 10px 12px;
    border: none;
    background: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    color: #475569;
    transition: all 0.15s ease;
    text-align: left;
}

.lang-option:hover {
    background: #f8fafc;
    color: #1e293b;
}

.lang-option.active {
    background: #f5f3ff;
    color: #7c3aed;
}

.lang-code {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 22px;
    border-radius: 6px;
    background: #f8fafc;
    color: #7c3aed;
    font-size: 11px;
    font-weight: 700;
    line-height: 1;
}

.lang-name {
    flex: 1;
    font-weight: 500;
}

.lang-check {
    font-size: 12px;
    color: #7c3aed;
}
</style>
