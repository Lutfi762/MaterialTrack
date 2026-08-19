<template>
    <Head><title>{{ item.name }} - MaterialTrack</title></Head>

    <div class="item-show-page">
        <div class="page-header">
            <div class="header-left">
                <a :href="'/apps/items'" class="back-link">
                    <i class="fa fa-arrow-left"></i> {{ $t('common.back') }}
                </a>
                <h4 class="page-title">{{ item.name }}</h4>
                <span class="item-code">{{ item.code }}</span>
            </div>
            <div class="header-actions">
                <a :href="'/apps/items/' + item.id + '/history'" class="btn-action btn-history">
                    <i class="fa fa-stream"></i> {{ $t('items.history.title') }}
                </a>
                <a :href="'/apps/items/' + item.id + '/edit'" class="btn-action btn-edit">
                    <i class="fa fa-edit"></i> {{ $t('common.edit') }}
                </a>
            </div>
        </div>

        <div class="content-grid">
            <div class="info-card">
                <h6 class="card-title"><i class="fa fa-info-circle"></i> {{ $t('items.detail_info') }}</h6>
                <div class="info-grid">
                    <div class="info-item">
                        <label>{{ $t('items.col_code') }}</label>
                        <span>{{ item.code }}</span>
                    </div>
                    <div class="info-item">
                        <label>{{ $t('items.col_name') }}</label>
                        <span>{{ item.name }}</span>
                    </div>
                    <div class="info-item">
                        <label>{{ $t('items.col_category') }}</label>
                        <span>{{ item.category?.name || '-' }}</span>
                    </div>
                    <div class="info-item">
                        <label>{{ $t('items.col_location') }}</label>
                        <span>{{ item.location?.name || '-' }}</span>
                    </div>
                    <div class="info-item">
                        <label>{{ $t('items.unit_label') }}</label>
                        <span>{{ item.unit }}</span>
                    </div>
                    <div class="info-item" v-if="item.unit_alt">
                        <label>{{ $t('items.unit_alt_label') }}</label>
                        <span>{{ item.unit_alt }} ({{ item.conversion_factor }} {{ item.unit }})</span>
                    </div>
                    <div class="info-item">
                        <label>{{ $t('items.stock_now') }}</label>
                        <span class="stock-value" :class="stockStatusClass">{{ item.current_stock }}</span>
                    </div>
                    <div class="info-item">
                        <label>{{ $t('items.stock_min_label') }}</label>
                        <span>{{ item.min_stock }}</span>
                    </div>
                    <div class="info-item full-width" v-if="item.description">
                        <label>{{ $t('common.description') }}</label>
                        <span>{{ item.description }}</span>
                    </div>
                </div>
            </div>

            <div class="qr-card">
                <h6 class="card-title"><i class="fa fa-qrcode"></i> QR Code</h6>
                <div class="qr-display">
                    <canvas ref="qrCanvas"></canvas>
                </div>
                <p class="qr-hint">{{ $t('items.qr_scan_hint') }}</p>
                <button @click="printQR" class="btn-print">
                    <i class="fa fa-print"></i> {{ $t('common.print_label') }}
                </button>
            </div>

            <div v-if="item.photos && item.photos.length" class="photos-card">
                <h6 class="card-title"><i class="fa fa-images"></i> {{ $t('items.photos') }}</h6>
                <div class="d-flex gap-2 flex-wrap">
                    <img v-for="(photo, index) in item.photos" :key="index"
                         :src="'/storage/' + photo" class="rounded"
                         style="width: 100px; height: 100px; object-fit: cover; cursor: pointer;"
                         @click="previewImage('/storage/' + photo)">
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Head } from '@inertiajs/vue3';
import LayoutApp from '@/Layouts/App.vue';
import QRCode from 'qrcode';

export default {
    layout: LayoutApp,
    components: { Head },
    props: {
        item: Object,
    },
    computed: {
        stockStatusClass() {
            if (this.item.current_stock <= 0) return 'status-habis';
            if (this.item.current_stock <= this.item.min_stock) return 'status-menipis';
            return 'status-aman';
        },
    },
    mounted() {
        this.generateQR();
    },
    methods: {
        generateQR() {
            QRCode.toCanvas(this.$refs.qrCanvas, this.item.code, {
                width: 200,
                margin: 2,
                color: {
                    dark: '#1e293b',
                    light: '#ffffff',
                },
            });
        },
        previewImage(src) {
            window.open(src, '_blank');
        },
        printQR() {
            const canvas = this.$refs.qrCanvas;
            if (!canvas) return;
            const dataUrl = canvas.toDataURL('image/png');
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head><title>QR - ${this.item.code}</title></head>
                <body style="text-align:center; padding:20px; font-family:Arial,sans-serif;">
                    <img src="${dataUrl}" style="width:200px;height:200px;">
                    <p style="margin:8px 0 0;font-size:14px;font-weight:bold;">${this.item.code}</p>
                    <p style="margin:2px 0 0;font-size:12px;color:#666;">${this.item.name}</p>
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        },
    },
};
</script>

<style scoped>
.item-show-page {
    max-width: 1000px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
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

.btn-action {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.btn-edit {
    background: #f5f3ff;
    color: #7c3aed;
    border: 1.5px solid #e0e7ff;
}

.btn-edit:hover {
    background: #ede9fe;
}

.btn-history {
    background: #f0fdf4;
    color: #16a34a;
    border: 1.5px solid #bbf7d0;
}

.btn-history:hover {
    background: #dcfce7;
}

.content-grid {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 20px;
}

.info-card, .qr-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.card-title {
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    margin: 0 0 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.info-item label {
    display: block;
    font-size: 11px;
    color: #94a3b8;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

.info-item span {
    font-size: 14px;
    color: #1e293b;
}

.info-item.full-width {
    grid-column: 1 / -1;
}

.stock-value {
    font-weight: 700;
    font-size: 18px;
}

.status-aman { color: #16a34a; }
.status-menipis { color: #f59e0b; }
.status-habis { color: #dc2626; }

.photos-card {
    grid-column: 1 / -1;
}

.qr-card {
    text-align: center;
}

.qr-display {
    display: flex;
    justify-content: center;
    padding: 16px 0;
}

.qr-display canvas {
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.qr-hint {
    font-size: 12px;
    color: #94a3b8;
    margin: 8px 0 16px;
}

.btn-print {
    background: white;
    border: 1.5px solid #e5e7eb;
    color: #374151;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.btn-print:hover {
    background: #f8fafc;
    border-color: #c7d2fe;
    color: #7c3aed;
}

@media (max-width: 768px) {
    .content-grid {
        grid-template-columns: 1fr;
    }

    .page-header {
        flex-direction: column;
        gap: 12px;
    }
}
</style>
