<template>
  <Head title="Login - MaterialTrack" />

  <div class="col-md-5 col-lg-4">
    <div class="text-center mb-4">
      <h3 class="fw-bold text-primary mb-1">
        <i class="fa fa-boxes-stacked me-2"></i>MaterialTrack
      </h3>
      <p class="text-muted small">Sistem Manajemen Stok & Pengeluaran Material</p>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
      <div class="card-body p-4">
        <div class="text-start mb-3">
          <h5 class="fw-bold mb-1">LOGIN AKUN</h5>
          <p class="text-muted small">Masukkan kredensial Anda untuk masuk ke sistem</p>
        </div>

        <hr class="text-muted opacity-25">

        <!-- Status Alert / Session Flash -->
        <div v-if="$page.props.session?.status" class="alert alert-success alert-dismissible fade show small" role="alert">
          {{ $page.props.session.status }}
        </div>

        <form @submit.prevent="submit">
          <!-- Input Email -->
          <div class="mb-3">
            <label class="form-label small fw-bold">Alamat Email</label>
            <div class="input-group">
              <span class="input-group-text bg-light"><i class="fa fa-envelope text-muted"></i></span>
              <input 
                v-model="form.email" 
                type="email" 
                class="form-control" 
                :class="{ 'is-invalid': form.errors.email }" 
                placeholder="nama@materialtrack.com"
                required
              />
            </div>
            <div v-if="form.errors.email" class="invalid-feedback d-block small mt-1">
              {{ form.errors.email }}
            </div>
          </div>

          <!-- Input Password -->
          <div class="mb-3">
            <label class="form-label small fw-bold">Password</label>
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

          <!-- Option Remember Me & Forgot Password -->
          <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
              <input v-model="form.remember" class="form-check-input" type="checkbox" id="remember" />
              <label class="form-check-label small" for="remember">Ingat Saya</label>
            </div>
            <Link href="/forgot-password" class="text-decoration-none small text-primary">Lupa Password?</Link>
          </div>

          <!-- Tombol Submit -->
          <button 
            type="submit" 
            class="btn btn-primary w-100 fw-bold py-2 shadow-sm"
            :disabled="form.processing"
          >
            <span v-if="form.processing" class="spinner-border spinner-border-sm me-2" role="status"></span>
            {{ form.processing ? 'MEMPROSES...' : 'MASUK KE SISTEM' }}
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
import { Head, Link, useForm } from '@inertiajs/vue3'
import LayoutAuth from '@/Layouts/Auth.vue'

// Tentukan Layout untuk halaman ini
defineOptions({
  layout: LayoutAuth,
})

// Form State menggunakan useForm bawaan Inertia Vue 3
const form = useForm({
  email: '',
  password: '',
  remember: false,
})

// Handler Submit ke Fortify (/login)
const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  })
}
</script>