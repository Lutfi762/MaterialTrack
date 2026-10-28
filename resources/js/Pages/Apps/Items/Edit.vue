<template>
    <Head><title>{{ $t('items.edit') }} - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-3xl mx-auto">
            <div class="mtx-card">
                <div class="mtx-card-header">
                    <span class="mtx-card-title"><i class="fa fa-box text-indigo-600" aria-hidden="true"></i> {{ $t('items.edit') }}</span>
                    <Link href="/apps/items" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
                        <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
                    </Link>
                </div>
                <div class="mtx-card-body">
                    <p class="mtx-hint mb-4">
                        <i class="fa fa-circle-info" aria-hidden="true"></i>
                        {{ $t('items.locked_desc') }}
                    </p>

                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('items.cat_locked') }}</label>
                                <input class="mtx-input" :value="item.category?.name" disabled>
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('items.name') }}</label>
                                <input class="mtx-input" v-model="form.name" :class="{ 'mtx-input-error': form.errors.name }" type="text">
                                <p v-if="form.errors.name" class="mtx-error-text">{{ form.errors.name }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('items.location_label') }}</label>
                                <select class="mtx-select" v-model="form.location_id">
                                    <option :value="null">{{ $t('items.location_none') }}</option>
                                    <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                                </select>
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('items.warehouse_label') }}</label>
                                <select class="mtx-select" v-model="form.warehouse_id">
                                    <option :value="null">{{ $t('items.warehouse_none') }}</option>
                                    <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
                                </select>
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('items.unit_label') }}</label>
                                <select class="mtx-select" v-model="form.unit" :class="{ 'mtx-select-error': form.errors.unit }">
                                    <option v-for="u in unitOptions" :key="u" :value="u">{{ u }}</option>
                                </select>
                                <p v-if="form.errors.unit" class="mtx-error-text">{{ form.errors.unit }}</p>
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('items.stock_min_label') }}</label>
                                <input class="mtx-input" v-model.number="form.min_stock" :class="{ 'mtx-input-error': form.errors.min_stock }" type="number" min="0">
                                <p v-if="form.errors.min_stock" class="mtx-error-text">{{ form.errors.min_stock }}</p>
                            </div>
                        </div>

                        <p class="mtx-hint border-t border-gray-100 pt-4">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> {{ $t('items.conversion_desc') }}
                        </p>

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('items.price') }}</label>
                            <input type="number" class="mtx-input" v-model="form.price" min="0" step="0.01" placeholder="0">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('items.unit_alt') }}</label>
                                <input class="mtx-input" v-model="form.unit_alt" :class="{ 'mtx-input-error': form.errors.unit_alt }" type="text" :placeholder="$t('items.unit_alt_placeholder')">
                                <p v-if="form.errors.unit_alt" class="mtx-error-text">{{ form.errors.unit_alt }}</p>
                            </div>
                            <div class="mtx-field">
                                <label class="mtx-label">{{ $t('items.conversion_factor') }}</label>
                                <div class="flex items-center gap-2">
                                    <input class="mtx-input" v-model.number="form.conversion_factor" :class="{ 'mtx-input-error': form.errors.conversion_factor }" type="number" min="0.01" step="0.01" placeholder="12">
                                    <span class="text-sm text-gray-500 shrink-0">{{ form.unit || '...' }}</span>
                                </div>
                                <p v-if="form.errors.conversion_factor" class="mtx-error-text">{{ form.errors.conversion_factor }}</p>
                                <p class="mtx-hint">1 {{ form.unit_alt || '?' }} = ? {{ form.unit || '...' }}</p>
                            </div>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('common.description') }}</label>
                            <textarea class="mtx-textarea" v-model="form.description" rows="2"></textarea>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('items.new_image') }}</label>
                            <input class="mtx-input" type="file" accept="image/*" @change="onImageChange">
                            <p v-if="form.errors.image" class="mtx-error-text">{{ form.errors.image }}</p>
                            <img v-if="item.image" :src="item.image" class="mt-2 rounded" style="max-height:80px">
                        </div>

                        <div v-if="existingPhotos.length" class="mtx-field">
                            <label class="mtx-label">{{ $t('items.current_photos') }}</label>
                            <div class="flex gap-2 flex-wrap">
                                <div v-for="photo in existingPhotos" :key="photo" class="relative">
                                    <img :src="'/storage/' + photo" class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                                    <button type="button" class="mtx-icon-btn mtx-icon-btn-danger absolute -top-2 -right-2 h-5 w-5" @click="removePhoto(photo)">
                                        <i class="fa fa-times text-xs" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mtx-field">
                            <label class="mtx-label">{{ $t('items.new_photo') }}</label>
                            <input type="file" class="mtx-input" @change="form.new_photos = $event.target.files" multiple accept="image/*">
                            <p class="mtx-hint">{{ $t('items.photos_desc') }}</p>
                        </div>

                        <button class="mtx-btn mtx-btn-primary" type="submit" :disabled="form.processing">
                            <span v-if="form.processing">{{ $t('common.saving') }}</span>
                            <span v-else>{{ $t('common.update') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Swal from 'sweetalert2';

export default {
    layout: LayoutApp,
    components: { Head, Link },
    props: { item: Object, locations: Array, warehouses: Array, unitOptions: Array },
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
            new_photos: [],
            removed_photos: [],
        });

        const existingPhotos = ref([...(props.item.photos ?? [])]);

        const onImageChange = (e) => {
            form.image = e.target.files[0] ?? null;
        };

        const removePhoto = (path) => {
            existingPhotos.value = existingPhotos.value.filter(p => p !== path);
            form.removed_photos.push(path);
        };

        const submit = () => {
            form.post(`/apps/items/${props.item.id}`, {
                onSuccess: () => Swal.fire({ title: t('common.saved_success'), text: t('items.edit'), icon: 'success', showConfirmButton: false, timer: 2000 }),
                onError: () => {
                    Swal.fire({
                        title: t('common.save_error'),
                        text: t('common.save_error_desc'),
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        };

        return { form, existingPhotos, onImageChange, removePhoto, submit };
    },
};
</script>
