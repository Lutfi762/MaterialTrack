<template>
    <Head>
        <title>Input Material Keluar - MaterialTrack</title>
    </Head>
    <main class="c-main">
        <div class="container-fluid">
            <div class="fade-in">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border-0 rounded-3 shadow border-top-danger">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <span class="font-weight-bold"><i class="fa fa-arrow-up-right-from-square me-1"></i> INPUT MATERIAL KELUAR</span>
                                <Link href="/apps/stock-outs" class="btn btn-sm btn-secondary">
                                    <i class="fa fa-arrow-left me-1"></i> Kembali
                                </Link>
                            </div>
                            <div class="card-body">

                                <form @submit.prevent="submit">

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Department <span class="text-danger">*</span></label>
                                            <select
                                                class="form-select"
                                                v-model="form.department_id"
                                                :class="{ 'is-invalid': form.errors.department_id }"
                                                :disabled="isDepartmentLocked"
                                                @change="onDepartmentChange"
                                                required
                                            >
                                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                                    {{ dept.name }}
                                                </option>
                                            </select>
                                            <div v-if="form.errors.department_id" class="invalid-feedback d-block">{{ form.errors.department_id }}</div>
                                            <small v-if="isDepartmentLocked" class="text-muted">
                                                <i class="fa fa-lock me-1"></i> Otomatis sesuai departemen akun Anda
                                            </small>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Kategori</label>
                                            <select
                                                class="form-select"
                                                v-model="selectedCategory"
                                                @change="onCategoryChange"
                                            >
                                                <option value="">- Semua Kategori -</option>
                                                <option v-for="cat in availableCategories" :key="cat.id" :value="cat.id">
                                                    {{ cat.name }}
                                                </option>
                                            </select>
                                            <small class="text-muted">Menyaring daftar material di bawah.</small>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Material <span class="text-danger">*</span></label>
                                            <select
                                                class="form-select"
                                                v-model="form.item_id"
                                                :class="{ 'is-invalid': form.errors.item_id }"
                                                @change="onItemChange"
                                                required
                                            >
                                                <option :value="null" disabled>- Pilih Material -</option>
                                                <option v-for="item in filteredItems" :key="item.id" :value="item.id">
                                                    {{ item.name }} ({{ item.code }}) — Stok: {{ item.current_stock }} {{ item.unit }}
                                                </option>
                                            </select>
                                            <div v-if="form.errors.item_id" class="invalid-feedback d-block">{{ form.errors.item_id }}</div>
                                            <small v-if="filteredItems.length === 0" class="text-danger">
                                                Tidak ada material untuk kombinasi department/kategori ini.
                                            </small>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">Quantity <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input
                                                    class="form-control"
                                                    v-model.number="form.qty"
                                                    :class="{ 'is-invalid': form.errors.qty }"
                                                    type="number"
                                                    min="1"
                                                    :max="selectedItem?.current_stock ?? null"
                                                    required
                                                >
                                                <span class="input-group-text">{{ selectedItem?.unit ?? 'unit' }}</span>
                                            </div>
                                            <div v-if="form.errors.qty" class="invalid-feedback d-block">{{ form.errors.qty }}</div>
                                            <small v-if="selectedItem" class="text-muted">
                                                Stok tersedia: {{ selectedItem.current_stock }} {{ selectedItem.unit }}
                                            </small>
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="fw-bold">PIC Pengambil Barang <span class="text-danger">*</span></label>
                                            <select
                                                class="form-select"
                                                v-model="form.pic_id"
                                                :class="{ 'is-invalid': form.errors.pic_id }"
                                                required
                                            >
                                                <option :value="null" disabled>- Pilih PIC -</option>
                                                <option v-for="pic in filteredPics" :key="pic.id" :value="pic.id">
                                                    {{ pic.name }}
                                                </option>
                                            </select>
                                            <div v-if="form.errors.pic_id" class="invalid-feedback d-block">{{ form.errors.pic_id }}</div>
                                            <small v-if="filteredPics.length === 0" class="text-danger">
                                                Belum ada PIC terdaftar untuk departemen ini. Hubungi Admin.
                                            </small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="fw-bold">Keperluan Penggunaan <span class="text-danger">*</span></label>
                                        <input
                                            class="form-control"
                                            v-model="form.purpose"
                                            :class="{ 'is-invalid': form.errors.purpose }"
                                            type="text"
                                            placeholder="Contoh: Maintenance Mesin A"
                                            required
                                        >
                                        <div v-if="form.errors.purpose" class="invalid-feedback d-block">{{ form.errors.purpose }}</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="fw-bold">Catatan Tambahan (opsional)</label>
                                        <textarea
                                            class="form-control"
                                            v-model="form.notes"
                                            rows="2"
                                        ></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="fw-bold">Tanggal Pengambilan <span class="text-danger">*</span></label>
                                        <input
                                            class="form-control"
                                            v-model="form.date"
                                            :class="{ 'is-invalid': form.errors.date }"
                                            type="date"
                                            required
                                        >
                                        <div v-if="form.errors.date" class="invalid-feedback d-block">{{ form.errors.date }}</div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <button
                                                class="btn btn-primary shadow-sm rounded-sm"
                                                type="submit"
                                                :disabled="form.processing || filteredPics.length === 0"
                                            >
                                                <span v-if="form.processing">Menyimpan...</span>
                                                <span v-else>SIMPAN</span>
                                            </button>
                                        </div>
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
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Swal from 'sweetalert2';

export default {
    layout: LayoutApp,

    components: {
        Head,
        Link,
    },

    props: {
        items: Array,
        departments: Array,
        pics: Array,
        lockedDepartmentId: [Number, null],
    },

    setup(props) {
        const isDepartmentLocked = props.lockedDepartmentId !== null;

        const form = useForm({
            item_id: null,
            qty: 1,
            department_id: isDepartmentLocked ? props.lockedDepartmentId : (props.departments[0]?.id ?? null),
            pic_id: null,
            purpose: '',
            notes: '',
            date: new Date().toISOString().slice(0, 10),
        });

        const selectedCategory = ref('');

        // Item yang kategorinya terkait dengan department yang sedang dipilih.
        const itemsInDepartment = computed(() => {
            if (!form.department_id) return props.items;
            return props.items.filter(item =>
                (item.department_ids ?? []).includes(form.department_id)
            );
        });

        // Daftar kategori unik dari item-item yang tersedia untuk department ini.
        const availableCategories = computed(() => {
            const map = new Map();
            itemsInDepartment.value.forEach(item => {
                if (item.category_id) {
                    map.set(item.category_id, { id: item.category_id, name: item.category_name });
                }
            });
            return Array.from(map.values());
        });

        // Item final: sudah disaring department, lalu disaring lagi kategori (kalau dipilih).
        const filteredItems = computed(() => {
            if (!selectedCategory.value) return itemsInDepartment.value;
            return itemsInDepartment.value.filter(item => item.category_id === selectedCategory.value);
        });

        const selectedItem = computed(() =>
            props.items.find(i => i.id === form.item_id) ?? null
        );

        const filteredPics = computed(() =>
            props.pics.filter(p => p.department_id === form.department_id)
        );

        const onDepartmentChange = () => {
            // Reset kategori & material setiap kali department berubah,
            // karena daftar yang tersedia berubah total.
            selectedCategory.value = '';
            form.item_id = null;
            form.pic_id = null;
        };

        const onCategoryChange = () => {
            form.item_id = null;
        };

        const onItemChange = () => {
            form.qty = 1;
        };

        const submit = () => {
            form.post('/apps/stock-outs', {
                onSuccess: () => {
                    const page = usePage();
                    const newId = page.props.newStockOutId;

                    Swal.fire({
                        title: 'Berhasil!',
                        text: newId
                            ? 'Material keluar berhasil dicatat. Struk sedang diunduh...'
                            : 'Material keluar berhasil dicatat.',
                        icon: 'success',
                        showConfirmButton: false,
                        timer: 2000,
                    });

                    if (newId) {
                        window.open(`/apps/stock-outs/${newId}/pdf`, '_blank');
                    }
                },
                onError: () => {
                    Swal.fire({
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat menyimpan. Periksa kembali form Anda.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        };

        return {
            form,
            selectedCategory,
            availableCategories,
            filteredItems,
            selectedItem,
            filteredPics,
            isDepartmentLocked,
            onDepartmentChange,
            onCategoryChange,
            onItemChange,
            submit,
        };
    },
}
</script>

<style>
.border-top-danger {
    border-top: 3px solid #dc3545 !important;
}
</style>