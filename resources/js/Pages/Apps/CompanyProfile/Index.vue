<template>
  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Pengaturan Perusahaan
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block font-medium text-sm text-gray-700">Nama Perusahaan *</label>
                <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                <span v-if="errors.name" class="text-xs text-red-600">{{ errors.name }}</span>
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Nama Legal</label>
                <input v-model="form.legal_name" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">NPWP / Tax ID</label>
                <input v-model="form.tax_id" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Mata Uang Utama</label>
                <input v-model="form.currency" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Email Perusahaan</label>
                <input v-model="form.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Telepon Perusahaan</label>
                <input v-model="form.phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>

              <div class="md:col-span-2">
                <label class="block font-medium text-sm text-gray-700">Alamat Lengkap</label>
                <textarea v-model="form.address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Kota</label>
                <input v-model="form.city" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>

              <div>
                <label class="block font-medium text-sm text-gray-700">Provinsi</label>
                <input v-model="form.province" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
              </div>
            </div>

            <div class="mt-6 flex justify-end">
              <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition">
                Simpan Perubahan
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/App.vue';

export default {
  components: { AppLayout },
  props: {
    company: Object,
    errors: Object,
  },
  data() {
    return {
      form: this.$inertia.form({
        name: this.company?.name || '',
        legal_name: this.company?.legal_name || '',
        tax_id: this.company?.tax_id || '',
        email: this.company?.email || '',
        phone: this.company?.phone || '',
        address: this.company?.address || '',
        city: this.company?.city || '',
        province: this.company?.province || '',
        postal_code: this.company?.postal_code || '',
        country: this.company?.country || 'Indonesia',
        website: this.company?.website || '',
        currency: this.company?.currency || 'IDR',
      }),
    };
  },
  methods: {
    submit() {
      this.form.post(route('apps.company-profile.update'));
    },
  },
};
</script>
