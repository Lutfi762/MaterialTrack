<template>
    <Head><title>Edit Lokasi - MaterialTrack</title></Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card border-0 rounded-3 shadow">
                            <div class="card-header"><span class="font-weight-bold">{{ $t('locations.edit') }}</span></div>
                            <div class="card-body">
                                <form @submit.prevent="submit">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">{{ $t('locations.name') }} <span class="text-danger">*</span></label>
                                        <input v-model="form.name" type="text" class="form-control" :class="{ 'is-invalid': form.errors.name }">
                                        <div class="invalid-feedback">{{ form.errors.name }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">{{ $t('locations.code') }}</label>
                                        <input v-model="form.code" type="text" class="form-control" :class="{ 'is-invalid': form.errors.code }">
                                        <div class="invalid-feedback">{{ form.errors.code }}</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">{{ $t('locations.address') }}</label>
                                        <textarea v-model="form.address" class="form-control" rows="3"></textarea>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-warning" :disabled="form.processing">Update</button>
                                        <Link href="/apps/locations" class="btn btn-secondary">{{ $t('common.cancel') }}</Link>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
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
    props: { location: Object },
    setup(props) {
        const form = useForm({
            name: props.location.name,
            code: props.location.code ?? '',
            address: props.location.address ?? '',
        });
        const submit = () => form.put(`/apps/locations/${props.location.id}`);
        return { form, submit };
    }
}
</script>
