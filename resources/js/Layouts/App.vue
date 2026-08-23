<template>
    <div class="app-wrapper">
        <Sidebar @toggle="onToggle" />

        <div class="main-area" :class="{ 'main-collapsed': collapsed }">
            <Header />

            <div class="page-header" v-if="$slots.header">
                <slot name="header" />
            </div>

            <div class="main-content">
                <transition name="fade">
                    <div v-if="flash" class="global-flash" :class="flash.type === 'error' ? 'global-flash-error' : 'global-flash-success'">
                        <i :class="flash.type === 'error' ? 'fa fa-circle-exclamation' : 'fa fa-circle-check'" aria-hidden="true"></i>
                        {{ flash.message }}
                    </div>
                </transition>
                <slot />
            </div>

            <footer class="main-footer">
                MaterialTrack &copy; 2026 — Sistem Manajemen Stok & Pengeluaran Material Gudang
            </footer>
        </div>

        <FloatingAIChat />
        <KeyboardShortcutsHelp />
    </div>
</template>

<script>
import Header from '../Components/Header.vue';
import Sidebar from '../Components/Sidebar.vue';
import FloatingAIChat from '../Components/FloatingAIChat.vue';
import KeyboardShortcutsHelp from '../Components/KeyboardShortcutsHelp.vue';
import { router } from '@inertiajs/vue3';
import { initKeyboardShortcuts } from '../Utils/keyboardShortcuts';

export default {
    components: { Header, Sidebar, FloatingAIChat, KeyboardShortcutsHelp },
    data() {
        return { collapsed: false, flash: null, flashTimer: null };
    },
    computed: {
        sessionFlash() {
            const session = this.$page.props.session || {};
            if (session.success) return { type: 'success', message: session.success };
            if (session.error) return { type: 'error', message: session.error };
            const errors = this.$page.props.errors || {};
            if (errors.error) return { type: 'error', message: errors.error };
            return null;
        },
    },
    watch: {
        sessionFlash(f) {
            if (this.flashTimer) clearTimeout(this.flashTimer);
            this.flash = f;
            if (f) {
                this.flashTimer = setTimeout(() => { this.flash = null; }, 6000);
            }
        },
    },
    methods: {
        onToggle(state) {
            this.collapsed = state;
        }
    },
    mounted() {
        this.flash = this.sessionFlash;
        if (this.flash) this.flashTimer = setTimeout(() => { this.flash = null; }, 6000);
        initKeyboardShortcuts(router);
    }
}
</script>

<style>
*, *::before, *::after { box-sizing: border-box; }

body {
    background: var(--color-bg);
    font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
    margin: 0;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

.app-wrapper {
    display: flex;
    min-height: 100vh;
}

.main-area {
    margin-left: 260px;
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    transition: margin-left 0.3s ease;
}

.main-area.main-collapsed {
    margin-left: 70px;
}

.main-content {
    flex: 1;
    padding: 24px;
}

.global-flash {
    display: flex;
    align-items: center;
    gap: 8px;
    position: sticky;
    top: 12px;
    z-index: 50;
    margin-bottom: 16px;
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
}

.global-flash-success {
    background: #ecfdf5;
    border: 1px solid #34d399;
    color: #065f46;
}

.global-flash-error {
    background: #fef2f2;
    border: 1px solid #f87171;
    color: #991b1b;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

.page-header {
    padding: 24px 24px 0;
    display: flex;
    align-items: center;
}

.main-footer {
    padding: 16px 24px;
    background: white;
    border-top: 1px solid #e5e7eb;
    font-size: 12px;
    color: #9ca3af;
    text-align: center;
}

/* Reset sisa CoreUI */
.c-main { padding: 0 !important; }
.fade-in { animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }

/* Mobile responsive */
@media (max-width: 768px) {
    .main-area {
        margin-left: 0 !important;
    }
    .main-content {
        padding: 16px;
    }
}

/* Dark Mode */
.dark body,
.dark .main-content {
    background: var(--color-bg);
    color: var(--color-on-surface);
}
.dark .card {
    background: #1e293b;
    border-color: #334155;
    color: var(--color-on-surface);
}
.dark .table {
    color: var(--color-on-surface);
}
.dark .table td,
.dark .table th {
    border-color: #334155;
}
.dark .form-control,
.dark .form-select {
    background: #1e293b;
    border-color: #475569;
    color: var(--color-on-surface);
}
.dark .page-title {
    color: var(--color-on-surface);
}
.dark .text-muted {
    color: var(--color-on-surface-variant) !important;
}
.dark .main-footer {
    background: #1e293b;
    border-color: #334155;
}
.dark .modern-header {
    background: #1e293b;
    border-color: #334155;
}
.dark .search-input {
    background: #0f172a;
    border-color: #334155;
    color: var(--color-on-surface);
}
.dark .notification-btn {
    background: #0f172a;
    border-color: #334155;
    color: var(--color-on-surface-variant);
}
.dark .notification-panel {
    background: #1e293b;
    border-color: #334155;
}

/* Dark mode - mtx design system (Suppliers, Categories, Items, Locations,
   Warehouses, WarehouseMap, StockIns, StockOuts, StockTransfers, Notifications) */
.dark .mtx-card {
    background: #1e293b;
    border-color: #334155;
}
.dark .mtx-card-header,
.dark .mtx-modal-header,
.dark .mtx-modal-footer {
    border-color: #334155;
}
.dark .mtx-modal {
    background: #1e293b;
    border-color: #334155;
}
.dark .mtx-page-title,
.dark .mtx-card-title,
.dark .mtx-modal-title {
    color: var(--color-on-surface);
}
.dark .mtx-input,
.dark .mtx-select,
.dark .mtx-textarea {
    background: #0f172a;
    border-color: #475569;
    color: var(--color-on-surface);
}
.dark .mtx-label {
    color: var(--color-on-surface-variant);
}
.dark .mtx-btn-secondary {
    background: #1e293b;
    border-color: #475569;
    color: var(--color-on-surface);
}
.dark .mtx-btn-secondary:hover {
    background: #334155;
}
.dark .mtx-icon-btn {
    color: var(--color-on-surface-variant);
}
.dark .mtx-icon-btn:hover {
    background: #334155;
    color: var(--color-on-surface);
}
.dark .notification-header {
    background: #0f172a;
    border-color: #334155;
}
.dark .notification-header h4 {
    color: #f1f5f9;
}
.dark .notification-item {
    border-color: #334155;
}
.dark .notification-item:hover {
    background: #0f172a;
}
.dark .notification-footer {
    background: #0f172a;
    border-color: #334155;
}
.dark .dropdown-panel {
    background: #1e293b;
    border-color: #334155;
}
.dark .dropdown-header {
    background: #0f172a;
    border-color: #334155;
}
.dark .dropdown-user-name {
    color: #f1f5f9;
}
.dark .dropdown-profile-link {
    color: var(--color-on-surface);
    border-color: #334155;
}
.dark .page-header-card,
.dark .filter-bar,
.dark .transaction-card,
.dark .alert-config-card {
    background: #1e293b;
    border-color: #334155;
}
.dark .transaction-item-name,
.dark .alert-config-name {
    color: #f1f5f9;
}
.dark .meta-badge {
    background: #0f172a;
    color: var(--color-on-surface-variant);
}
.dark .empty-state {
    background: #1e293b;
    border-color: #334155;
}
.dark .lang-btn {
    background: #0f172a;
    border-color: #334155;
    color: var(--color-on-surface-variant);
}
.dark .theme-toggle {
    background: #0f172a;
    border-color: #334155;
    color: #94a3b8;
}

/* ============================================================
   Global Tailwind-utility dark overrides.
   Covers the bulk of Pages Vue files built with plain Tailwind
   classes (bg-white, text-gray-etc, border-gray-etc, ...) so
   every page gets dark mode without per-page markup edits.
   ponytail: blanket utility override, not per-component theming.
   Upgrade to token-based classes (bg-surface, text-on-surface)
   page by page when doing a full visual redesign of that page.
   ============================================================ */
.dark .bg-white { background-color: var(--color-surface) !important; }
.dark .bg-gray-50,
.dark .bg-gray-100 { background-color: #1e1f26 !important; }
.dark .bg-gray-200 { background-color: #282a30 !important; }
.dark .text-gray-900,
.dark .text-gray-800,
.dark .text-gray-700,
.dark .text-slate-900,
.dark .text-slate-800,
.dark .text-slate-700,
.dark .text-black { color: var(--color-on-surface) !important; }
.dark .text-gray-600,
.dark .text-gray-500,
.dark .text-gray-400,
.dark .text-slate-600,
.dark .text-slate-500,
.dark .text-slate-400,
.dark .text-zinc-600,
.dark .text-zinc-500,
.dark .text-neutral-600,
.dark .text-neutral-500,
.dark .text-muted { color: var(--color-on-surface-variant) !important; }
.dark .placeholder\:text-gray-500::placeholder,
.dark .placeholder\:text-gray-400::placeholder,
.dark input::placeholder,
.dark textarea::placeholder { color: var(--color-outline) !important; opacity: 1; }
.dark :disabled { color: var(--color-outline) !important; }
.dark .border-gray-100,
.dark .border-gray-200,
.dark .border-gray-300,
.dark .border-gray-400,
.dark .divide-gray-200 { border-color: #33343b !important; }
.dark .hover\:bg-gray-50:hover,
.dark .hover\:bg-gray-100:hover,
.dark .hover\:bg-gray-200:hover,
.dark .hover\:bg-gray-300:hover { background-color: #282a30 !important; }
.dark input.form-control,
.dark select.form-select,
.dark textarea.form-control,
.dark .search-input {
    background-color: #12141c;
    border-color: #33343b;
    color: #e2e2eb;
}

/* Semantic color families used across Pages (indigo=primary, red=danger,
   green=success, yellow/amber=warning, blue=info) remapped to dark tokens
   so buttons/badges/alerts stay legible without per-page edits. */
.dark [class*="bg-indigo-"] { background-color: var(--color-primary-2) !important; color: #12141c !important; }
.dark [class*="text-indigo-"] { color: var(--color-primary) !important; }
.dark [class*="border-indigo-"] { border-color: var(--color-primary-2) !important; }
.dark [class*="bg-red-"] { background-color: #3a1520 !important; color: #ffb4c0 !important; }
.dark [class*="text-red-"] { color: #ff8fa3 !important; }
.dark [class*="border-red-"] { border-color: var(--color-danger) !important; }
.dark [class*="bg-green-"] { background-color: #10301f !important; color: #8fe9c0 !important; }
.dark [class*="text-green-"] { color: #6ee7b7 !important; }
.dark [class*="border-green-"] { border-color: var(--color-success) !important; }
.dark [class*="bg-yellow-"],
.dark [class*="bg-amber-"] { background-color: #3a2e0a !important; color: #ffd782 !important; }
.dark [class*="text-yellow-"],
.dark [class*="text-amber-"] { color: #fbbf24 !important; }
.dark [class*="border-yellow-"],
.dark [class*="border-amber-"] { border-color: var(--color-warning) !important; }
.dark [class*="bg-blue-"] { background-color: #0f2a3a !important; color: #93cdfa !important; }
.dark [class*="text-blue-"] { color: #7dd3fc !important; }
</style>