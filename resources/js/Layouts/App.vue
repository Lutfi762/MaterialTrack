<template>
    <div class="app-wrapper">
        <Sidebar @toggle="onToggle" />

        <div class="main-area" :class="{ 'main-collapsed': collapsed }">
            <Header />

            <div class="main-content">
                <slot />
            </div>

            <footer class="main-footer">
                MaterialTrack &copy; 2026 — Sistem Manajemen Stok & Pengeluaran Material Gudang
            </footer>
        </div>

        <FloatingAIChat />
    </div>
</template>

<script>
import Header from '../Components/Header.vue';
import Sidebar from '../Components/Sidebar.vue';
import FloatingAIChat from '../Components/FloatingAIChat.vue';

export default {
    components: { Header, Sidebar, FloatingAIChat },
    data() {
        return { collapsed: false };
    },
    methods: {
        onToggle(state) {
            this.collapsed = state;
        }
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
</style>