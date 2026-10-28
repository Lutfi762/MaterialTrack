<template>
    <Head><title>{{ $t('locations.add') }} - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="mtx-card">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-map-marker-alt text-indigo-600" aria-hidden="true"></i> {{ $t('locations.add') }}</span>
                    <Link href="/apps/locations" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                    </Link>
                </div>
                <div class="mtx-card-body">
                    <form @submit.prevent="submit">
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('locations.name') }} <span class="mtx-required">*</span></label>
                            <input v-model="form.name" type="text" class="mtx-input" :class="{ 'mtx-input-error': form.errors.name }">
                            <p v-if="form.errors.name" class="mtx-error-text">{{ form.errors.name }}</p>
                        </div>
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('locations.code') }}</label>
                            <input v-model="form.code" type="text" class="mtx-input" :class="{ 'mtx-input-error': form.errors.code }">
                            <p v-if="form.errors.code" class="mtx-error-text">{{ form.errors.code }}</p>
                        </div>
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('locations.warehouse_zone') }}</label>
                            <input v-model="form.warehouse_zone" type="text" class="mtx-input" :class="{ 'mtx-input-error': form.errors.warehouse_zone }">
                            <p v-if="form.errors.warehouse_zone" class="mtx-error-text">{{ form.errors.warehouse_zone }}</p>
                        </div>
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('locations.address') }}</label>
                            <textarea v-model="form.address" class="mtx-textarea" rows="3"></textarea>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="mtx-btn mtx-btn-primary" :disabled="form.processing">{{ $t('common.save') }}</button>
                            <Link href="/apps/locations" class="mtx-btn mtx-btn-secondary">{{ $t('common.cancel') }}</Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

export default {
    layout: LayoutApp,
    components: { Head, Link },
    setup() {
        const { t } = useI18n();
        const form = useForm({ name: '', code: '', warehouse_zone: '', address: '' });
        const submit = () => form.post('/apps/locations');
        return { form, submit };
    }
}
</script>
