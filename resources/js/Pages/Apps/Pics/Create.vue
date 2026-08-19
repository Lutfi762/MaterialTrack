<template>
    <Head><title>Tambah PIC - MaterialTrack</title></Head>
    <main>
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-7">
                    <div class="form-card">
                        <div class="form-card-header">
                            <Link href="/apps/pics" class="back-btn"><i class="fa fa-arrow-left"></i></Link>
                            <div>
                                <h5 class="mb-0">{{ $t('pics.add_heading') }}</h5>
                                <p class="text-muted small mb-0">{{ $t('pics.add_desc') }}</p>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="form-card-body">
                            <div class="form-floating-modern mb-3">
                                <input class="form-control" v-model="form.name" :class="{ 'is-invalid': form.errors.name }" type="text" placeholder=" ">
                                <label>{{ $t('pics.full_name') }}</label>
                                <div v-if="form.errors.name" class="invalid-feedback d-block">{{ form.errors.name }}</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating-modern">
                                        <input class="form-control" v-model="form.nik" :class="{ 'is-invalid': form.errors.nik }" type="text" placeholder=" ">
                                        <label>{{ $t('pics.nik') }}</label>
                                        <div v-if="form.errors.nik" class="invalid-feedback d-block">{{ form.errors.nik }}</div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-floating-modern">
                                        <input class="form-control" v-model="form.phone" type="text" placeholder=" ">
                                        <label>{{ $t('pics.phone') }}</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-floating-modern mb-3">
                                <input class="form-control" v-model="form.email" :class="{ 'is-invalid': form.errors.email }" type="email" placeholder=" ">
                                <label>{{ $t('pics.email') }}</label>
                                <div v-if="form.errors.email" class="invalid-feedback d-block">{{ form.errors.email }}</div>
                            </div>

                            <div class="mb-4">
                                <label class="fw-semibold mb-2 d-block">Departemen</label>
                                <select class="form-select form-select-modern" v-model="form.department_id" :disabled="lockedDepartmentId !== null">
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                                </select>
                                <small v-if="lockedDepartmentId !== null" class="text-muted d-block mt-1">
                                    <i class="fa fa-lock me-1"></i> {{ $t('pics.department_auto') }}
                                </small>
                            </div>

                            <button class="btn btn-primary btn-modern w-100" type="submit" :disabled="form.processing">
                                <span v-if="form.processing"><i class="fa fa-spinner fa-spin me-2"></i>{{ $t('common.saving') }}</span>
                                <span v-else><i class="fa fa-check me-2"></i>{{ $t('pics.save') }}</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script>
import LayoutApp from '../../../Layouts/App.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

export default {
    layout: LayoutApp,
    components: { Head, Link },
    props: { departments: Array, lockedDepartmentId: [Number, null] },
    setup(props) {
        const form = useForm({
            name: '',
            nik: '',
            phone: '',
            email: '',
            department_id: props.lockedDepartmentId ?? (props.departments[0]?.id ?? null),
        });

        const submit = () => {
            form.post('/apps/pics', {
                onSuccess: () => Swal.fire({
                    title: $t('common.saved_success'),
                    text: 'PIC baru berhasil ditambahkan.',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 2000,
                }),
            });
        };

        return { form, submit };
    },
};
</script>

<style scoped>
.form-card {
    background: white;
    border-radius: 18px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    overflow: hidden;
}
.form-card-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 22px 26px;
    background: linear-gradient(135deg, #eef4ff, #ffffff);
    border-bottom: 1px solid #eef1f6;
}
.back-btn {
    width: 36px; height: 36px;
    border-radius: 10px;
    background: white;
    display: flex; align-items: center; justify-content: center;
    color: #0d6efd;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    flex-shrink: 0;
}
.form-card-body { padding: 26px; }

.form-floating-modern {
    position: relative;
}
.form-floating-modern .form-control {
    height: 54px;
    border-radius: 10px;
    border: 1.5px solid #e5e9f0;
    padding: 20px 14px 6px;
}
.form-floating-modern .form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
}
.form-floating-modern label {
    position: absolute;
    top: 6px;
    left: 14px;
    font-size: 11px;
    color: #6b7280;
    font-weight: 600;
    pointer-events: none;
}

.form-select-modern {
    height: 48px;
    border-radius: 10px;
    border: 1.5px solid #e5e9f0;
}

.btn-modern {
    border-radius: 10px;
    padding: 12px;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25);
    border: none;
}
</style>
