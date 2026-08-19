<template>
    <Head><title>{{ $t('stock_alert.title') }} - MaterialTrack</title></Head>
    <div class="stockalerts-page">

        <div class="page-header-card mb-4">
            <div>
                <h4 class="mb-1"><i class="fa fa-bell me-2 text-primary"></i>{{ $t('stock_alert.title') }}</h4>
                <p class="text-muted mb-0 small">{{ $t('stock_alert.subtitle') }}</p>
            </div>
        </div>

        <div class="alert-config-list">
            <div v-for="config in localConfigs" :key="config.id" class="alert-config-card">
                <div class="alert-config-header">
                    <div class="alert-config-icon" :class="`icon-${config.alert_type}`">
                        <i :class="getAlertIcon(config.alert_type)"></i>
                    </div>
                    <div class="alert-config-info">
                        <div class="alert-config-name">{{ getAlertLabel(config.alert_type) }}</div>
                        <div class="alert-config-desc">{{ getAlertDesc(config.alert_type) }}</div>
                    </div>
                </div>

                <div class="alert-config-body">
                    <div class="toggle-row">
                        <div class="toggle-label">
                            <i class="fa fa-envelope me-2 text-muted"></i>
                            {{ $t('stock_alert.email_alerts') }}
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" v-model="config.email_enabled">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="toggle-row">
                        <div class="toggle-label">
                            <i class="fa fa-globe me-2 text-muted"></i>
                            {{ $t('stock_alert.browser_alerts') }}
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" v-model="config.browser_enabled">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div v-if="config.alert_type === 'expiry'" class="toggle-row">
                        <div class="toggle-label">
                            <i class="fa fa-calendar me-2 text-muted"></i>
                            {{ $t('stock_alert.threshold_days') }}
                        </div>
                        <div class="threshold-input">
                            <input type="number" v-model.number="config.threshold_days" min="1" max="365" class="form-control form-control-sm" style="width: 80px;">
                            <span class="ms-2 text-muted small">{{ $t('stock_alert.days') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button class="btn btn-primary px-4" @click="saveConfigs" :disabled="isSaving">
                <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="fa fa-save me-2"></i>
                {{ $t('common.save') }}
            </button>
        </div>
    </div>
</template>

<script>
import LayoutApp from '../../../Layouts/App.vue';
import { Head } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';
import { useI18n } from 'vue-i18n';
import Swal from 'sweetalert2';

export default {
    layout: LayoutApp,
    components: { Head },
    props: { configs: Array },
    setup(props) {
        const { t } = useI18n();
        const isSaving = ref(false);

        const localConfigs = reactive(
            props.configs.map(c => ({
                ...c,
                email_enabled: Boolean(c.email_enabled),
                browser_enabled: Boolean(c.browser_enabled),
            }))
        );

        const getAlertIcon = (type) => {
            const icons = {
                low_stock: 'fa fa-exclamation-triangle',
                expiry: 'fa fa-clock',
                min_stock: 'fa fa-layer-group',
            };
            return icons[type] || 'fa fa-bell';
        };

        const getAlertLabel = (type) => {
            const labels = {
                low_stock: t('stock_alert.type_low_stock'),
                expiry: t('stock_alert.type_expiry'),
                min_stock: t('stock_alert.type_min_stock'),
            };
            return labels[type] || type;
        };

        const getAlertDesc = (type) => {
            const descs = {
                low_stock: t('stock_alert.desc_low_stock'),
                expiry: t('stock_alert.desc_expiry'),
                min_stock: t('stock_alert.desc_min_stock'),
            };
            return descs[type] || '';
        };

        const saveConfigs = async () => {
            isSaving.value = true;
            try {
                const response = await fetch('/apps/stock-alerts', {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ configs: localConfigs }),
                });

                if (response.ok) {
                    Swal.fire({ title: t('common.saved_success'), icon: 'success', showConfirmButton: false, timer: 2000 });
                } else {
                    Swal.fire({ title: t('common.save_error'), icon: 'error', confirmButtonText: 'OK' });
                }
            } catch (e) {
                Swal.fire({ title: t('common.save_error'), icon: 'error', confirmButtonText: 'OK' });
            } finally {
                isSaving.value = false;
            }
        };

        return { localConfigs, isSaving, getAlertIcon, getAlertLabel, getAlertDesc, saveConfigs };
    },
};
</script>

<style scoped>
.page-header-card {
    background: white; border-radius: 16px; padding: 22px 26px;
    display: flex; justify-content: space-between; align-items: center;
    flex-wrap: wrap; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.alert-config-list { display: flex; flex-direction: column; gap: 16px; }

.alert-config-card {
    background: white; border-radius: 14px; padding: 20px 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    transition: box-shadow 0.2s ease;
}
.alert-config-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }

.alert-config-header { display: flex; gap: 16px; align-items: center; margin-bottom: 16px; }
.alert-config-icon {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 18px;
}
.icon-low_stock { background: #fef3c7; color: #d97706; }
.icon-expiry { background: #fee2e2; color: #dc2626; }
.icon-min_stock { background: #e0e7ff; color: #6366f1; }

.alert-config-name { font-size: 16px; font-weight: 700; color: #1e293b; }
.alert-config-desc { font-size: 13px; color: #64748b; margin-top: 2px; }

.alert-config-body { display: flex; flex-direction: column; gap: 14px; }

.toggle-row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 10px 14px; background: #f8fafc; border-radius: 10px;
}
.toggle-label { font-size: 13px; font-weight: 500; color: #334155; display: flex; align-items: center; }
.threshold-input { display: flex; align-items: center; }

.toggle-switch {
    position: relative; display: inline-block; width: 44px; height: 24px;
}
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider {
    position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
    background-color: #cbd5e1; border-radius: 24px; transition: 0.3s;
}
.toggle-slider::before {
    position: absolute; content: ""; height: 18px; width: 18px;
    left: 3px; bottom: 3px; background-color: white; border-radius: 50%;
    transition: 0.3s; box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.toggle-switch input:checked + .toggle-slider { background-color: #7c3aed; }
.toggle-switch input:checked + .toggle-slider::before { transform: translateX(20px); }
</style>
