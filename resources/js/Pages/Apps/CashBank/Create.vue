<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catat Transaksi Kas &amp; Bank</h2>
        <Link :href="route('apps.cash-bank.index')" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md text-sm font-medium">Kembali</Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <div v-if="$page.props.errors.error" class="mb-4 p-3 bg-red-50 text-red-700 border border-red-200 rounded-md text-sm">
            {{ $page.props.errors.error }}
          </div>

          <form @submit.prevent="submit">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Transaksi *</label>
                <div class="flex gap-2">
                  <button type="button" @click="form.type = 'in'"
                          class="flex-1 py-2 rounded-md border text-sm font-medium"
                          :class="form.type === 'in' ? 'bg-green-600 border-green-600 text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                    &#8595; Kas Masuk
                  </button>
                  <button type="button" @click="form.type = 'out'"
                          class="flex-1 py-2 rounded-md border text-sm font-medium"
                          :class="form.type === 'out' ? 'bg-red-600 border-red-600 text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                    &#8593; Kas Keluar
                  </button>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal *</label>
                <input type="date" v-model="form.transaction_date" class="w-full border rounded-md px-3 py-2 text-sm" />
                <p v-if="$page.props.errors.transaction_date" class="text-xs text-red-600 mt-1">{{ $page.props.errors.transaction_date }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Akun Kas/Bank *</label>
                <select v-model="form.account_id" class="w-full border rounded-md px-3 py-2 text-sm">
                  <option value="">-- Pilih Akun --</option>
                  <option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.code }} - {{ a.name }}</option>
                </select>
                <p v-if="$page.props.errors.account_id" class="text-xs text-red-600 mt-1">{{ $page.props.errors.account_id }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Akun Lawan *</label>
                <select v-model="form.counter_account_id" class="w-full border rounded-md px-3 py-2 text-sm">
                  <option value="">-- Pilih Akun --</option>
                  <option v-for="a in counterAccounts" :key="a.id" :value="a.id">{{ a.code }} - {{ a.name }} ({{ a.type }})</option>
                </select>
                <p v-if="$page.props.errors.counter_account_id" class="text-xs text-red-600 mt-1">{{ $page.props.errors.counter_account_id }}</p>
                <p class="text-xs text-gray-400 mt-1">
                  {{ form.type === 'in' ? 'Kas masuk: akun kas di-DEBIT, akun lawan di-KREDIT.' : 'Kas keluar: akun kas di-KREDIT, akun lawan di-DEBIT.' }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nominal (Rp) *</label>
                <input type="number" min="1" step="0.01" v-model="form.amount" class="w-full border rounded-md px-3 py-2 text-sm" />
                <p v-if="$page.props.errors.amount" class="text-xs text-red-600 mt-1">{{ $page.props.errors.amount }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No. Referensi</label>
                <input type="text" v-model="form.reference_no" placeholder="opsional" class="w-full border rounded-md px-3 py-2 text-sm" />
              </div>
            </div>

            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
              <textarea v-model="form.description" rows="2" class="w-full border rounded-md px-3 py-2 text-sm"
                        placeholder="catatan transaksi"></textarea>
            </div>

            <div class="mt-6 flex justify-end">
              <button type="submit" :disabled="form.processing"
                      class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium disabled:opacity-50">
                {{ form.processing ? 'Menyimpan...' : 'Simpan Transaksi' }}
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
import { Link } from '@inertiajs/vue3';

export default {
  components: { AppLayout, Link },
  props: { accounts: Array, counterAccounts: Array },
  data() {
    return {
      form: this.$inertia.form({
        transaction_date: new Date().toISOString().slice(0, 10),
        type: 'in',
        account_id: '',
        counter_account_id: '',
        amount: '',
        reference_no: '',
        description: '',
      }),
    };
  },
  methods: {
    submit() {
      this.form.post(route('apps.cash-bank.store'), {
        preserveScroll: true,
        onSuccess: () => this.form.reset('amount', 'reference_no', 'description'),
      });
    },
  },
};
</script>