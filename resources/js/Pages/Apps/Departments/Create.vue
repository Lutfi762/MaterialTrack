<template>
    <Head><title>Tambah Department - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="mtx-card">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-building text-indigo-600" aria-hidden="true"></i> {{ $t('departments.add') }}</span>
                    <Link href="/apps/departments" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                    </Link>
                </div>
                <div class="mtx-card-body">
                    <form @submit.prevent="submit">
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('departments.name') }} <span class="mtx-required">*</span></label>
                            <input v-model="form.name" type="text" class="mtx-input" :class="{ 'mtx-input-error': form.errors.name }" placeholder="Contoh: Produksi">
                            <p v-if="form.errors.name" class="mtx-error-text">{{ form.errors.name }}</p>
                        </div>
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('departments.code') }}</label>
                            <input v-model="form.code" type="text" class="mtx-input" :class="{ 'mtx-input-error': form.errors.code }" placeholder="Contoh: PROD">
                            <p v-if="form.errors.code" class="mtx-error-text">{{ form.errors.code }}</p>
                        </div>
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('common.status') }}</label>
                            <select v-model="form.status" class="mtx-select" :class="{ 'mtx-select-error': form.errors.status }">
                                <option value="active">{{ $t('common.active') }}</option>
                                <option value="inactive">{{ $t('common.inactive') }}</option>
                            </select>
                            <p v-if="form.errors.status" class="mtx-error-text">{{ form.errors.status }}</p>
                        </div>
                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('common.description') }}</label>
                            <textarea v-model="form.description" class="mtx-textarea" rows="3" :placeholder="$t('departments.description_placeholder')"></textarea>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <button type="submit" class="mtx-btn mtx-btn-primary" :disabled="form.processing">
                                <span v-if="form.processing">{{ $t('common.saving') }}</span>
                                <span v-else>{{ $t('common.save') }}</span>
                            </button>
                            <Link href="/apps/departments" class="mtx-btn mtx-btn-secondary">{{ $t('common.cancel') }}</Link>
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

export default {
    layout: LayoutApp,
    components: { Head, Link },
    setup() {
        const form = useForm({ name: '', code: '', description: '', status: 'active' });
        const submit = () => form.post('/apps/departments');
        return { form, submit };
    }
}
</script>
