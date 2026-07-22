<template>
  <Head title="Reset Password - MaterialTrack" />

  <div class="col-md-5 col-lg-4">
    <div class="text-center mb-4">
      <h3 class="fw-bold text-primary mb-1">
        <i class="fa fa-boxes-stacked me-2"></i>MaterialTrack
      </h3>
      <p class="text-muted small">Sistem Manajemen Stok & Pengeluaran Material</p>
    </div>

    <div class="card border-0 shadow-sm rounded-3 bg-white">
      <div class="card-body p-4">
        <div class="text-start mb-3">
          <h5 class="fw-bold mb-1">PASSWORD BARU</h5>
          <p class="text-muted small">Silahkan masukkan password baru Anda.</p>
        </div>

        <hr class="text-muted opacity-25">

        <form @submit.prevent="submit">
          <div class="mb-3">
            <label class="form-label small fw-bold">Alamat Email</label>
            <div class="input-group">
              <span class="input-group-text bg-light"><i class="fa fa-envelope text-muted"></i></span>
              <input 
                v-model="form.email" 
                type="email" 
                class="form-control bg-light" 
                readonly
              />
            </div>
            <div v-if="form.errors.email" class="invalid-feedback d-block small mt-1">
              {{ form.errors.email }}
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold">Password Baru</label>
            <div class="input-group">
              <span class="input-group-text bg-light"><i class="fa fa-lock text-muted"></i></span>
              <input 
                v-model="form.password" 
                type="password" 
                class="form-control" 
                :class="{ 'is-invalid': form.errors.password }" 
                placeholder="••••••••"
                required
              />
            </div>
            <div v-if="form.errors.password" class="invalid-feedback d-block small mt-1">
              {{ form.errors.password }}
            </div>
          </div>

          <div class="mb-4">
            <label class="form-label small fw-bold">Konfirmasi Password Baru</label>
            <div class="input-group">
              <span class="input-group-text bg-light"><i class="fa fa-lock text-muted"></i></span>
              <input 
                v-model="form.password_confirmation" 
                type="password" 
                class="form-control" 
                placeholder="••••••••"
                required
              />
            </div>
          </div>

          <button 
            type="submit" 
            class="btn btn-primary w-100 fw-bold py-2 shadow-sm"
            :disabled="form.processing"
          >
            <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
            {{ form.processing ? 'MENYIMPAN...' : 'UPDATE PASSWORD' }}
          </button>
        </form>
      </div>
    </div>

    <div class="text-center mt-4 text-muted small">
      &copy; 2026 <strong>MaterialTrack</strong>. All rights reserved.
    </div>
  </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import LayoutAuth from '@/Layouts/Auth.vue'

defineOptions({
  layout: LayoutAuth,
})

// Menerima props dari Fortify / Inertia
const props = defineProps({
  request: Object,
  token: String,
  email: String,
})

// Inisialisasi Form: Mengambil email & token dari props atau URL secara presisi
const urlParams = new URLSearchParams(window.location.search)
const currentPath = window.location.pathname.split('/')
const tokenFromPath = currentPath[currentPath.length - 1]

const form = useForm({
  token: props.token || props.request?.route?.params?.token || tokenFromPath || '',
  email: props.email || props.request?.query?.email || urlParams.get('email') || 'admin@materialtrack.com',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post('/reset-password', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>