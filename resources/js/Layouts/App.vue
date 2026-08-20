<template>
    <div class="app-wrapper">
        <Sidebar @toggle="onToggle" />

        <div class="main-area" :class="{ 'main-collapsed': collapsed }">
            <Header />

            <div class="page-header" v-if="$slots.header">
                <slot name="header" />
            </div>

            <div class="main-content">
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
        return { collapsed: false };
    },
    methods: {
        onToggle(state) {
            this.collapsed = state;
        }
    },
    mounted() {
        initKeyboardShortcuts(router);
    }
}
</script>

<style>
*, *::before, *::after { box-sizing: border-box; }

body {
    background: #f9fafb;
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
.dark-mode body,
.dark-mode .main-content {
    background: #0f172a;
    color: #e2e8f0;
}
.dark-mode .card {
    background: #1e293b;
    border-color: #334155;
    color: #e2e8f0;
}
.dark-mode .table {
    color: #e2e8f0;
}
.dark-mode .table td,
.dark-mode .table th {
    border-color: #334155;
}
.dark-mode .form-control,
.dark-mode .form-select {
    background: #1e293b;
    border-color: #475569;
    color: #e2e8f0;
}
.dark-mode .page-title {
    color: #f1f5f9;
}
.dark-mode .text-muted {
    color: #94a3b8 !important;
}
.dark-mode .main-footer {
    background: #1e293b;
    border-color: #334155;
}
.dark-mode .modern-header {
    background: #1e293b;
    border-color: #334155;
}
.dark-mode .search-input {
    background: #0f172a;
    border-color: #334155;
    color: #e2e8f0;
}
.dark-mode .notification-btn {
    background: #0f172a;
    border-color: #334155;
    color: #94a3b8;
}
.dark-mode .notification-panel {
    background: #1e293b;
    border-color: #334155;
}
.dark-mode .notification-header {
    background: #0f172a;
    border-color: #334155;
}
.dark-mode .notification-header h4 {
    color: #f1f5f9;
}
.dark-mode .notification-item {
    border-color: #334155;
}
.dark-mode .notification-item:hover {
    background: #0f172a;
}
.dark-mode .notification-footer {
    background: #0f172a;
    border-color: #334155;
}
.dark-mode .dropdown-panel {
    background: #1e293b;
    border-color: #334155;
}
.dark-mode .dropdown-header {
    background: #0f172a;
    border-color: #334155;
}
.dark-mode .dropdown-user-name {
    color: #f1f5f9;
}
.dark-mode .dropdown-profile-link {
    color: #e2e8f0;
    border-color: #334155;
}
.dark-mode .page-header-card,
.dark-mode .filter-bar,
.dark-mode .transaction-card,
.dark-mode .alert-config-card {
    background: #1e293b;
    border-color: #334155;
}
.dark-mode .transaction-item-name,
.dark-mode .alert-config-name {
    color: #f1f5f9;
}
.dark-mode .meta-badge {
    background: #0f172a;
    color: #94a3b8;
}
.dark-mode .empty-state {
    background: #1e293b;
    border-color: #334155;
}
.dark-mode .lang-btn {
    background: #0f172a;
    border-color: #334155;
    color: #94a3b8;
}
.dark-mode .theme-toggle {
    background: #0f172a;
    border-color: #334155;
    color: #94a3b8;
}
</style>