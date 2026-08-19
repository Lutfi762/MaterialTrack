<template>
    <Head><title>{{ $t('items.history.title') }} - {{ item.name }}</title></Head>

    <div class="history-page">
        <div class="page-header">
            <div class="header-left">
                <a :href="'/apps/items/' + item.id" class="back-link">
                    <i class="fa fa-arrow-left"></i> {{ $t('common.back') }}
                </a>
                <h4 class="page-title">{{ $t('items.history.title') }}</h4>
                <span class="item-code">{{ item.code }}</span>
            </div>
        </div>

        <div class="timeline-card">
            <h6 class="card-title"><i class="fa fa-stream"></i> {{ $t('items.history.subtitle') }}</h6>

            <div v-if="movements.data.length === 0" class="empty-state">
                <i class="fa fa-inbox"></i>
                <p>{{ $t('items.history.empty') }}</p>
            </div>

            <div v-else class="timeline">
                <div v-for="movement in movements.data" :key="movement.id" class="timeline-item">
                    <div class="timeline-dot" :class="dotClass(movement.type)"></div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <span class="timeline-date">{{ movement.created_at }}</span>
                            <span class="timeline-badge" :class="badgeClass(movement.type)">
                                {{ $t('stock_movements.type_' + movement.type) }}
                            </span>
                        </div>
                        <div class="timeline-body">
                            <div class="movement-qty">
                                <span class="qty-label">{{ $t('common.quantity') }}:</span>
                                <span class="qty-value" :class="qtyClass(movement.type)">
                                    {{ movement.type === 'out' || movement.type === 'adjustment' ? '-' : '+' }}{{ movement.qty }}
                                </span>
                            </div>
                            <div class="movement-stocks">
                                <div class="stock-item">
                                    <span class="stock-label">{{ $t('items.history.stock_before') }}</span>
                                    <span class="stock-val">{{ movement.stock_before }}</span>
                                </div>
                                <i class="fa fa-arrow-right stock-arrow"></i>
                                <div class="stock-item">
                                    <span class="stock-label">{{ $t('items.history.stock_after') }}</span>
                                    <span class="stock-val">{{ movement.balance_after }}</span>
                                </div>
                            </div>
                            <p v-if="movement.notes" class="timeline-notes">
                                <i class="fa fa-comment-dots"></i> {{ movement.notes }}
                            </p>
                            <p class="timeline-user">
                                <i class="fa fa-user"></i> {{ movement.user?.name || '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <Pagination :links="movements.links" />
        </div>
    </div>
</template>

<script>
import { Head } from '@inertiajs/vue3';
import LayoutApp from '@/Layouts/App.vue';
import Pagination from '@/Components/Pagination.vue';

export default {
    layout: LayoutApp,
    components: { Head, Pagination },
    props: {
        item: Object,
        movements: Object,
    },
    methods: {
        dotClass(type) {
            const map = { in: 'dot-in', out: 'dot-out', opname: 'dot-opname', adjustment: 'dot-adjustment' };
            return map[type] || '';
        },
        badgeClass(type) {
            const map = { in: 'badge-in', out: 'badge-out', opname: 'badge-opname', adjustment: 'badge-adjustment' };
            return map[type] || '';
        },
        qtyClass(type) {
            const map = { in: 'qty-in', out: 'qty-out', opname: 'qty-opname', adjustment: 'qty-adjustment' };
            return map[type] || '';
        },
    },
};
</script>

<style scoped>
.history-page {
    max-width: 900px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 24px;
}

.back-link {
    font-size: 13px;
    color: #64748b;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
}

.back-link:hover {
    color: #7c3aed;
}

.page-title {
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 4px;
}

.item-code {
    font-size: 13px;
    color: #7c3aed;
    font-weight: 600;
    background: #f5f3ff;
    padding: 2px 10px;
    border-radius: 6px;
}

.timeline-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.card-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin: 0 0 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.empty-state {
    text-align: center;
    padding: 40px 0;
    color: #94a3b8;
}

.empty-state i {
    font-size: 32px;
    margin-bottom: 8px;
    display: block;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e5e7eb;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-dot {
    position: absolute;
    left: -26px;
    top: 4px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
}

.dot-in { background: #16a34a; }
.dot-out { background: #dc2626; }
.dot-opname { background: #2563eb; }
.dot-adjustment { background: #f59e0b; }

.timeline-content {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 14px 16px;
}

.timeline-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.timeline-date {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}

.timeline-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.badge-in { background: #dcfce7; color: #16a34a; }
.badge-out { background: #fef2f2; color: #dc2626; }
.badge-opname { background: #eff6ff; color: #2563eb; }
.badge-adjustment { background: #fffbeb; color: #d97706; }

.timeline-body {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.movement-qty {
    display: flex;
    align-items: center;
    gap: 8px;
}

.qty-label {
    font-size: 12px;
    color: #64748b;
}

.qty-value {
    font-size: 15px;
    font-weight: 700;
}

.qty-in { color: #16a34a; }
.qty-out { color: #dc2626; }
.qty-opname { color: #2563eb; }
.qty-adjustment { color: #d97706; }

.movement-stocks {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
}

.stock-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.stock-label {
    font-size: 10px;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    font-weight: 500;
}

.stock-val {
    font-weight: 600;
    color: #374151;
}

.stock-arrow {
    color: #cbd5e1;
    font-size: 11px;
}

.timeline-notes {
    font-size: 12px;
    color: #64748b;
    margin: 0;
}

.timeline-notes i {
    margin-right: 4px;
}

.timeline-user {
    font-size: 12px;
    color: #94a3b8;
    margin: 0;
}

.timeline-user i {
    margin-right: 4px;
}
</style>
