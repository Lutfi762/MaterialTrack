<template>
  <Head title="Forgot Password - MaterialTrack" />

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
          <h5 class="fw-bold mb-1">RESET PASSWORD</h5>
          <p class="text-muted small">Masukkan email Anda untuk menerima link reset password.</p>
        </div>

        <hr class="text-muted opacity-25">

        <!-- Notifikasi sukses kirim email -->
        <div v-if="$page.props.session?.status" class="alert alert-success border-0 rounded-3 small mb-3">
          <i class="fa fa-check-circle me-1"></i> {{ $page.props.session.status }}
        </div>

        <form @submit.prevent="submit">
          <div class="mb-3">
            <label class="form-label small fw-bold">Alamat Email</label>
            <div class="input-group">
              <span class="input-group-text bg-light"><i class="fa fa-envelope text-muted"></i></span>
              <input 
                v-model="form.email" 
                type="email" 
                class="form-control" 
                :class="{ 'is-invalid': form.errors.email }" 
                placeholder="admin@materialtrack.com"
                required
              />
            </div>
            <div v-if="form.errors.email" class="invalid-feedback d-block small mt-1">
              {{ form.errors.email }}
            </div>
          </div>

          <button 
            type="submit" 
            class="btn btn-primary w-100 fw-bold py-2 shadow-sm mb-3"
            :disabled="form.processing"
          >
            <span v-if="form.processing" class="spinner-border spinner-border-sm me-2"></span>
            {{ form.processing ? 'MENGIRIM EMAIL...' : 'KIRIM LINK RESET PASSWORD' }}
          </button>

          <div class="text-center">
            <Link href="/login" class="text-decoration-none small text-muted">
              <i class="fa fa-arrow-left me-1"></i> Kembali ke Login
            </Link>
          </div>
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

defineOptions({
  layout: LayoutAuth,
})

const form = useForm({
  email: '',
})

const submit = () => {
  form.post('/forgot-password')
}
</script>