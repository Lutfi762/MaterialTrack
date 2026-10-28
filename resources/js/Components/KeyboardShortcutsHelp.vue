<template>
    <Teleport to="body">
        <div v-if="visible" class="shortcuts-overlay" @click="hide">
            <div class="shortcuts-modal" @click.stop>
                <div class="shortcuts-header">
                    <h5 class="m-0 text-base font-bold"><i class="fa fa-keyboard me-2"></i>{{ $t('shortcuts.title') }}</h5>
                    <button class="mtx-icon-btn" @click="hide"><i class="fa fa-times"></i></button>
                </div>
                <div class="shortcuts-body">
                    <div class="shortcut-group">
                        <h6 class="shortcut-group-title">{{ $t('shortcuts.navigation') }}</h6>
                        <div class="shortcut-row" v-for="s in navigationShortcuts" :key="s.keys">
                            <span class="shortcut-keys"><kbd>{{ s.keys }}</kbd></span>
                            <span class="shortcut-desc">{{ s.desc }}</span>
                        </div>
                    </div>
                    <div class="shortcut-group">
                        <h6 class="shortcut-group-title">{{ $t('shortcuts.global') }}</h6>
                        <div class="shortcut-row" v-for="s in globalShortcuts" :key="s.keys">
                            <span class="shortcut-keys"><kbd>{{ s.keys }}</kbd></span>
                            <span class="shortcut-desc">{{ s.desc }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script>
export default {
    data() {
        return {
            visible: false,
            navigationShortcuts: [
                { keys: 'G â†’ D', desc: this.$t('shortcuts.go_dashboard') },
                { keys: 'G â†’ I', desc: this.$t('shortcuts.go_items') },
                { keys: 'G â†’ S', desc: this.$t('shortcuts.go_stock_in') },
                { keys: 'G â†’ O', desc: this.$t('shortcuts.go_stock_out') },
                { keys: 'G â†’ W', desc: this.$t('shortcuts.go_warehouses') },
                { keys: 'G â†’ R', desc: this.$t('shortcuts.go_reports') },
                { keys: 'G â†’ M', desc: this.$t('shortcuts.go_movements') },
                { keys: 'G â†’ F', desc: this.$t('shortcuts.go_forecasting') },
            ],
            globalShortcuts: [
                { keys: 'Ctrl + K', desc: this.$t('shortcuts.focus_search') },
                { keys: '?', desc: this.$t('shortcuts.show_help') },
                { keys: 'Esc', desc: this.$t('shortcuts.close_help') },
            ],
        };
    },
    mounted() {
        window.addEventListener('show-shortcuts-help', () => { this.visible = true; });
        window.addEventListener('hide-shortcuts-help', () => { this.visible = false; });
    },
    methods: {
        hide() {
            this.visible = false;
        },
    },
};
</script>

<style scoped>
.shortcuts-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(4px);
}
.shortcuts-modal {
    background: white;
    border-radius: 16px;
    width: 480px;
    max-height: 80vh;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(0,0,0,0.3);
}
.shortcuts-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #e5e7eb;
}
.shortcuts-body {
    padding: 16px 20px;
    overflow-y: auto;
    max-height: 60vh;
}
.shortcut-group {
    margin-bottom: 20px;
}
.shortcut-group-title {
    font-size: 11px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
}
.shortcut-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #f3f4f6;
}
.shortcut-row:last-child {
    border-bottom: none;
}
.shortcut-keys kbd {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 4px 10px;
    font-size: 12px;
    font-family: 'SF Mono', 'Consolas', monospace;
    color: #334155;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.shortcut-desc {
    font-size: 13px;
    color: #475569;
}
</style>
