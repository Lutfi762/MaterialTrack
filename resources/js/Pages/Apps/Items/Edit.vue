<template>
    <Head><title>{{ $t('items.edit') }} - MaterialTrack</title></Head>
    <main>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 rounded-3 shadow border-top-purple">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <span class="font-weight-bold"><i class="fa fa-box me-1"></i> {{ $t('items.edit') }}</span>
                            <Link href="/apps/items" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left me-1"></i> {{ $t('common.back') }}</Link>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fa fa-circle-info me-1"></i>
                                {{ $t('items.locked_desc') }}
                            </div>

                            <form @submit.prevent="submit">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">{{ $t('items.cat_locked') }}</label>
                                        <input class="form-control" :value="item.category?.name" disabled>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="fw-bold">{{ $t('items.name') }}</label>
                                        <input class="form-control" v-model="form.name" :class="{ 'is-invalid': form.errors.name }" type="text">
                                        <div v-if="form.errors.name" class="invalid-feedback d-block">{{ form.errors.name }}</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <label class="fw-bold">{{ $t('items.location_label') }}</label>
                                        <select class="form-select" v-model="form.location_id">
                                            <option :value="null">{{ $t('items.location_none') }}</option>
                                            <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="fw-bold">{{ $t('items.warehouse_label') }}</label>
                                        <select class="form-select" v-model="form.warehouse_id">
                                            <option :value="null">{{ $t('items.warehouse_none') }}</option>
                                            <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="fw-bold">{{ $t('items.unit_label') }}</label>
                                        <select class="form-select" v-model="form.unit" :class="{ 'is-invalid': form.errors.unit }">
                                            <option v-for="u in unitOptions" :key="u" :value="u">{{ u }}</option>
                                        </select>
                                        <div v-if="form.errors.unit" class="invalid-feedback d-block">{{ form.errors.unit }}</div>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label class="fw-bold">{{ $t('items.stock_min_label') }}</label>
                                        <input class="form-control" v-model.number="form.min_stock" :class="{ 'is-invalid': form.errors.min_stock }" type="number" min="0">
                                        <div v-if="form.errors.min_stock" class="invalid-feedback d-block">{{ form.errors.min_stock }}</div>
                                    </div>
                                </div>

                                <hr>
                                <p class="text-muted small mb-3"><i class="fa fa-info-circle me-1"></i> {{ $t('items.conversion_desc') }}</p>

                                <div class="mb-3">
                                    <label class="fw-bold">{{ $t('items.price') }}</label>
                                    <input type="number" class="form-control" v-model="form.price" min="0" step="0.01" placeholder="0">
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="fw-bold">{{ $t('items.unit_alt') }}</label>
                                        <input class="form-control" v-model="form.unit_alt" :class="{ 'is-invalid': form.errors.unit_alt }" type="text" :placeholder="$t('items.unit_alt_placeholder')">
                                        <div v-if="form.errors.unit_alt" class="invalid-feedback d-block">{{ form.errors.unit_alt }}</div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="fw-bold">{{ $t('items.conversion_factor') }}</label>
                                        <div class="input-group">
                                            <input class="form-control" v-model.number="form.conversion_factor" :class="{ 'is-invalid': form.errors.conversion_factor }" type="number" min="0.01" step="0.01" placeholder="12">
                                            <span class="input-group-text">{{ form.unit || '...' }}</span>
                                        </div>
                                        <div v-if="form.errors.conversion_factor" class="invalid-feedback d-block">{{ form.errors.conversion_factor }}</div>
                                        <small class="text-muted">1 {{ form.unit_alt || '?' }} = ? {{ form.unit || '...' }}</small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="fw-bold">{{ $t('common.description') }}</label>
                                    <textarea class="form-control" v-model="form.description" rows="2"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="fw-bold">{{ $t('items.new_image') }}</label>
                                    <input class="form-control" type="file" accept="image/*" @change="onImageChange">
                                    <div v-if="form.errors.image" class="invalid-feedback d-block">{{ form.errors.image }}</div>
                                    <img v-if="item.image" :src="item.image" class="mt-2 rounded" style="max-height:80px">
                                </div>

                                <div class="mb-3">
                                    <label class="fw-bold">{{ $t('items.photos') }}</label>
                                    <input type="file" class="form-control" @input="form.photos = $event.target.files" multiple accept="image/*">
                                    <small class="text-muted">{{ $t('items.photos_desc') }}</small>
                                </div>

                                <div v-if="form.photos && form.photos.length" class="mb-3">
                                    <label class="fw-bold">{{ $t('items.current_photos') }}</label>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <div v-for="(photo, index) in form.photos" :key="index" class="position-relative">
                                            <img :src="'/storage/' + photo" class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                                            <button type="button" class="btn btn-sm btn-danger position-absolute" style="top: -5px; right: -5px; width: 20px; height: 20px; font-size: 10px;" @click="removePhoto(index, photo)">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <button class="btn btn-primary shadow-sm rounded-sm" type="submit" :disabled="form.processing">
                                    <span v-if="form.processing">{{ $t('common.saving') }}</span>
                                    <span v-else>UPDATE</span>
                                </button>
                            </form>
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
import { useI18n } from 'vue-i18n';
import Swal from 'sweetalert2';

export default {
    layout: LayoutApp,
    components: { Head, Link },
    props: { item: Object, categories: Array, locations: Array, warehouses: Array, unitOptions: Array },
    setup(props) {
        const { t } = useI18n();
        const form = useForm({
            _method: 'put',
            category_id: props.item.category_id,
            name: props.item.name,
            location_id: props.item.location_id,
            warehouse_id: props.item.warehouse_id,
            unit: props.item.unit,
            unit_alt: props.item.unit_alt ?? '',
            conversion_factor: props.item.conversion_factor ?? null,
            min_stock: props.item.min_stock,
            price: props.item.price ?? null,
            description: props.item.description ?? '',
            image: null,
            photos: props.item.photos ?? [],
            removedPhotos: [],
        });

        const onImageChange = (e) => {
            form.image = e.target.files[0] ?? null;
        };

        const removePhoto = (index, path) => {
            form.photos.splice(index, 1);
            form.removedPhotos.push(path);
        };

        const submit = () => {
            form.post(`/apps/items/${props.item.id}`, {
                onSuccess: () => Swal.fire({ title: t('common.saved_success'), text: t('items.edit'), icon: 'success', showConfirmButton: false, timer: 2000 }),
            });
        };

        return { form, onImageChange, removePhoto, submit };
    },
};
</script>

<style>
.border-top-purple { border-top: 3px solid #6f42c1 !important; }
</style>