<template>
  <AppLayout>
    <Head><title>{{ current.title }} — MaterialTrack</title></Head>

    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ current.title }}</h2>
      </div>
    </template>

    <div class="erp-landing">
      <div class="erp-hero" :style="{ background: current.gradient }">
        <span class="erp-hero-icon">{{ current.icon }}</span>
        <div>
          <h1>{{ current.title }}</h1>
          <p>{{ current.subtitle }}</p>
        </div>
      </div>

      <div class="erp-grid">
        <Link
          v-for="m in visibleModules"
          :key="m.route"
          :href="route(m.route)"
          class="erp-card"
        >
          <span class="erp-card-icon">{{ m.icon }}</span>
          <span class="erp-card-body">
            <span class="erp-card-name">{{ m.name }}</span>
            <span class="erp-card-desc">{{ m.desc }}</span>
          </span>
          <span class="erp-card-arrow">→</span>
        </Link>
      </div>

      <div v-if="visibleModules.length === 0" class="erp-empty">
        Belum ada modul yang bisa diakses dalam grup ini.
      </div>
    </div>
  </AppLayout>
</template>
<script>
import AppLayout from '@/Layouts/App.vue';
import { Head, Link } from '@inertiajs/vue3';

const SECTIONS = {
    'pengadaan-penjualan': {
        title: 'ERP — Pengadaan & Penjualan',
        subtitle: 'Alur lengkap dari permintaan pembelian hingga faktur penjualan & retur.',
        icon: '🛒',
        gradient: 'linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%)',
        modules: [
            { route: 'apps.customers.index', perm: 'customers.index', icon: '👥', name: 'Pelanggan', desc: 'Master data pelanggan' },
            { route: 'apps.purchase-requests.index', perm: 'purchase_requests.index', icon: '📋', name: 'Purchase Requests', desc: 'Permintaan pembelian' },
            { route: 'apps.purchase-orders.index', perm: 'purchase_orders.index', icon: '🛍️', name: 'Purchase Orders', desc: 'Pesanan pembelian ke supplier' },
            { route: 'apps.goods-receipts.index', perm: 'goods_receipts.index', icon: '📦', name: 'Goods Receipts', desc: 'Penerimaan barang' },
            { route: 'apps.purchase-invoices.index', perm: 'purchase_invoices.index', icon: '🧾', name: 'Purchase Invoices', desc: 'Faktur pembelian' },
            { route: 'apps.quotations.index', perm: 'quotations.index', icon: '🏷️', name: 'Quotations', desc: 'Penawaran harga ke pelanggan' },
            { route: 'apps.sales-orders.index', perm: 'sales_orders.index', icon: '🛒', name: 'Sales Orders', desc: 'Pesanan penjualan' },
            { route: 'apps.deliveries.index', perm: 'deliveries.index', icon: '🚚', name: 'Surat Jalan', desc: 'Pengiriman barang' },
            { route: 'apps.sales-invoices.index', perm: 'sales_invoices.index', icon: '💳', name: 'Sales Invoices', desc: 'Faktur penjualan' },
            { route: 'apps.sales-returns.index', perm: 'sales_returns.index', icon: '↩️', name: 'Sales Returns', desc: 'Retur penjualan' },
        ],
    },
    'keuangan-hrm': {
        title: 'ERP — Keuangan & HRM',
        subtitle: 'Pembukuan dari kode akun, jurnal, laporan keuangan, hingga manajemen karyawan & payroll.',
        icon: '💼',
        gradient: 'linear-gradient(135deg, #059669 0%, #047857 100%)',
        modules: [
            { route: 'apps.chart-of-accounts.index', perm: 'chart_of_accounts.index', icon: '📊', name: 'Chart of Accounts', desc: 'Kode & kelompok akun' },
            { route: 'apps.journal-entries.index', perm: 'journal_entries.index', icon: '📖', name: 'Jurnal Umum', desc: 'Pencatatan transaksi' },
            { route: 'apps.financial-reports.index', perm: 'financial_reports.index', icon: '📑', name: 'Laporan Keuangan', desc: 'Laba rugi & neraca' },
            { route: 'apps.cash-bank.index', perm: 'cash_bank.index', icon: '💰', name: 'Kas & Bank', desc: 'Mutasi kas masuk / keluar' },
            { route: 'apps.employees.index', perm: 'employees.index', icon: '👨‍💼', name: 'Karyawan', desc: 'Master data pegawai' },
            { route: 'apps.attendances.index', perm: 'attendances.index', icon: '🕒', name: 'Presensi', desc: 'Kehadiran harian' },
            { route: 'apps.leaves.index', perm: 'leaves.index', icon: '🏖️', name: 'Cuti & Izin', desc: 'Pengajuan cuti & persetujuan' },
            { route: 'apps.payroll.index', perm: 'payroll.index', icon: '🧮', name: 'Penggajian', desc: 'Payroll & slip gaji' },
        ],
    },
    'sistem-alur': {
        title: 'ERP — Sistem & Alur',
        subtitle: 'Konfigurasi sistem, alur persetujuan, hak akses, dan jejak audit aktivitas.',
        icon: '⚙️',
        gradient: 'linear-gradient(135deg, #d97706 0%, #b45309 100%)',
        modules: [
            { route: 'apps.approval-workflows.index', perm: 'approval_workflows.index', icon: '✅', name: 'Approval Workflows', desc: 'Alur persetujuan dokumen' },
            { route: 'apps.company-profile.index', perm: 'company_profile.index', icon: '🏢', name: 'Profil Perusahaan', desc: 'Identitas & info perusahaan' },
            { route: 'apps.users.index', perm: 'users.index', icon: '🧑‍💻', name: 'Pengguna', desc: 'Manajemen user' },
            { route: 'apps.roles.index', perm: 'roles.index', icon: '🛡️', name: 'Peran (Roles)', desc: 'Grup hak akses' },
            { route: 'apps.permissions.index', perm: 'permissions.index', icon: '🔐', name: 'Izin (Permissions)', desc: 'Daftar permission sistem' },
            { route: 'apps.activity-logs', perm: 'activity_logs.index', icon: '📜', name: 'Audit Trail', desc: 'Riwayat aktivitas pengguna' },
            { route: 'apps.stock-alerts.index', perm: 'stock_alerts.index', icon: '⚠️', name: 'Notifikasi Stok', desc: 'Konfigurasi peringatan stok' },
        ],
    },
};

export default {
    components: { AppLayout, Head, Link },
    props: {
        section: { type: String, required: true },
    },
    computed: {
        current() {
            return SECTIONS[this.section] || SECTIONS['pengadaan-penjualan'];
        },
        visibleModules() {
            return this.current.modules.filter((m) => this.hasAnyPermission([m.perm]));
        },
    },
};
</script>
<style scoped>
.erp-landing {
    padding: 4px 0;
}

.erp-hero {
    display: flex;
    align-items: center;
    gap: 18px;
    border-radius: 14px;
    padding: 22px 26px;
    color: white;
    margin-bottom: 24px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.erp-hero-icon {
    font-size: 34px;
    width: 58px;
    height: 58px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.16);
    border-radius: 14px;
    flex-shrink: 0;
}

.erp-hero h1 {
    margin: 0 0 4px;
    font-size: 20px;
    font-weight: 700;
}

.erp-hero p {
    margin: 0;
    font-size: 13px;
    opacity: 0.85;
}

.erp-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 14px;
}

.erp-card {
    display: flex;
    align-items: center;
    gap: 12px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    text-decoration: none;
    color: #0f172a;
    transition: all 0.18s ease;
}

.erp-card:hover {
    border-color: #c7d2fe;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.07);
    transform: translateY(-2px);
}

.erp-card-icon {
    font-size: 22px;
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
    border-radius: 10px;
    flex-shrink: 0;
}

.erp-card-body {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
}

.erp-card-name {
    font-size: 14px;
    font-weight: 600;
    line-height: 1.3;
}

.erp-card-desc {
    font-size: 12px;
    color: #64748b;
    margin-top: 2px;
}

.erp-card-arrow {
    color: #94a3b8;
    font-size: 16px;
    flex-shrink: 0;
    transition: transform 0.18s ease;
}

.erp-card:hover .erp-card-arrow {
    transform: translateX(3px);
    color: #6d28d9;
}

.erp-empty {
    background: white;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    padding: 32px;
    text-align: center;
    color: #64748b;
    font-size: 14px;
}

@media (max-width: 640px) {
    .erp-grid {
        grid-template-columns: 1fr;
    }
    .erp-hero {
        padding: 18px;
    }
}
</style>
