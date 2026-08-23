<template>
    <Head><title>{{ $t('suppliers.add') }} - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="mtx-card">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-truck text-indigo-600" aria-hidden="true"></i> {{ $t('suppliers.add') }}</span>
                    <Link href="/apps/suppliers" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                    </Link>
                </div>
                <div class="mtx-card-body">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('suppliers.name') }} <span class="mtx-required">*</span></label>
                                <input v-model="form.name" type="text" class="mtx-input" :class="{ 'mtx-input-error': form.errors.name }">
                                <p v-if="form.errors.name" class="mtx-error-text">{{ form.errors.name }}</p>
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('suppliers.code') }}</label>
                                <input v-model="form.code" type="text" class="mtx-input" :class="{ 'mtx-input-error': form.errors.code }">
                                <p v-if="form.errors.code" class="mtx-error-text">{{ form.errors.code }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('suppliers.contact_person') }}</label>
                                <input v-model="form.contact_person" type="text" class="mtx-input">
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('suppliers.email') }}</label>
                                <input v-model="form.email" type="email" class="mtx-input" :class="{ 'mtx-input-error': form.errors.email }">
                                <p v-if="form.errors.email" class="mtx-error-text">{{ form.errors.email }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('suppliers.phone') }}</label>
                                <input v-model="form.phone" type="text" class="mtx-input">
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('suppliers.lead_time_days') }}</label>
                                <input v-model="form.lead_time_days" type="number" class="mtx-input" min="0">
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('suppliers.rating') }}</label>
                                <input v-model="form.rating" type="number" class="mtx-input" min="0" max="5" step="0.01">
                            </div>
                        </div>
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('suppliers.address') }}</label>
                            <textarea v-model="form.address" class="mtx-textarea" rows="2"></textarea>
                        </div>
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('suppliers.notes') }}</label>
                            <textarea v-model="form.notes" class="mtx-textarea" rows="2"></textarea>
                        </div>
                        <div class="mtx-field mtx-checkbox-row">
                            <input v-model="form.is_active" type="checkbox" class="mtx-checkbox" id="is_active">
                            <label class="mtx-checkbox-label" for="is_active">{{ $t('suppliers.is_active') }}</label>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="mtx-btn mtx-btn-primary" :disabled="form.processing">{{ $t('common.save') }}</button>
                            <Link href="/apps/suppliers" class="mtx-btn mtx-btn-secondary">{{ $t('common.cancel') }}</Link>
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
        const form = useForm({
            name: '',
            code: '',
            contact_person: '',
            email: '',
            phone: '',
            address: '',
            lead_time_days: 7,
            rating: 0,
            notes: '',
            is_active: true,
        });
        const submit = () => form.post('/apps/suppliers');
        return { form, submit };
    }
}
</script>
