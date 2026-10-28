<template>
  <Head><title>{{ $t('cash_bank.add') }} - MaterialTrack</title></Head>
  <main class="p-6">
    <div class="max-w-3xl mx-auto">
      <div class="mtx-card">
        <div class="mtx-card-header">
          <span class="mtx-card-title"><i class="fa fa-university text-indigo-600" aria-hidden="true"></i> {{ $t('cash_bank.add') }}</span>
          <Link href="/apps/cash-bank" class="mtx-btn mtx-btn-secondary mtx-btn-sm">
            <i class="fa fa-arrow-left" aria-hidden="true"></i> {{ $t('common.back') }}
          </Link>
        </div>
        <div class="mtx-card-body">
          <div v-if="$page.props.errors.error" class="mb-4 p-3 bg-red-50 text-red-700 border border-red-200 rounded-md text-sm">
            {{ $page.props.errors.error }}
          </div>

          <form @submit.prevent="submit">
            <div class="mtx-field">
              <label class="mtx-label">{{ $t('cash_bank.col_type') }} <span class="mtx-required">*</span></label>
              <div class="flex gap-2">
                <button type="button" @click="form.type = 'in'"
                        class="flex-1 py-2 rounded-md border text-sm font-medium transition-colors"
                        :class="form.type === 'in' ? 'bg-green-600 border-green-600 text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                  <i class="fa fa-arrow-down" aria-hidden="true"></i> {{ $t('cash_bank.type_in', 'Kas Masuk') }}
                </button>
                <button type="button" @click="form.type = 'out'"
                        class="flex-1 py-2 rounded-md border text-sm font-medium transition-colors"
                        :class="form.type === 'out' ? 'bg-red-600 border-red-600 text-white' : 'border-gray-300 text-gray-600 hover:bg-gray-50'">
                  <i class="fa fa-arrow-up" aria-hidden="true"></i> {{ $t('cash_bank.type_out', 'Kas Keluar') }}
                </button>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4">
              <div class="mtx-field">
                <label class="mtx-label">{{ $t('cash_bank.col_date') }} <span class="mtx-required">*</span></label>
                <input type="date" v-model="form.transaction_date" class="mtx-input" :class="{ 'mtx-input-error': form.errors.transaction_date }" />
                <p v-if="form.errors.transaction_date" class="mtx-error-text">{{ form.errors.transaction_date }}</p>
              </div>

              <div class="mtx-field">
                <label class="mtx-label">{{ $t('cash_bank.col_account') }} <span class="mtx-required">*</span></label>
                <select v-model="form.account_id" class="mtx-select" :class="{ 'mtx-select-error': form.errors.account_id }">
                  <option value="">-- {{ $t('common.select') }} --</option>
                  <option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.code }} - {{ a.name }}</option>
                </select>
                <p v-if="form.errors.account_id" class="mtx-error-text">{{ form.errors.account_id }}</p>
              </div>

              <div class="mtx-field">
                <label class="mtx-label">{{ $t('cash_bank.col_counter_account') }} <span class="mtx-required">*</span></label>
                <select v-model="form.counter_account_id" class="mtx-select" :class="{ 'mtx-select-error': form.errors.counter_account_id }">
                  <option value="">-- {{ $t('common.select') }} --</option>
                  <option v-for="a in counterAccounts" :key="a.id" :value="a.id">{{ a.code }} - {{ a.name }} ({{ a.type }})</option>
                </select>
                <p class="mtx-hint">
                  {{ form.type === 'in' ? 'Kas masuk: akun kas di-DEBIT, akun lawan di-KREDIT.' : 'Kas keluar: akun kas di-KREDIT, akun lawan di-DEBIT.' }}
                </p>
                <p v-if="form.errors.counter_account_id" class="mtx-error-text">{{ form.errors.counter_account_id }}</p>
              </div>

              <div class="mtx-field">
                <label class="mtx-label">{{ $t('cash_bank.col_amount') }} (Rp) <span class="mtx-required">*</span></label>
                <input type="number" min="1" step="0.01" v-model="form.amount" class="mtx-input" :class="{ 'mtx-input-error': form.errors.amount }" />
                <p v-if="form.errors.amount" class="mtx-error-text">{{ form.errors.amount }}</p>
              </div>

              <div class="mtx-field">
                <label class="mtx-label">No. Referensi</label>
                <input type="text" v-model="form.reference_no" placeholder="opsional" class="mtx-input" />
              </div>
            </div>

            <div class="mtx-field">
              <label class="mtx-label">{{ $t('cash_bank.col_description') }}</label>
              <textarea v-model="form.description" rows="2" class="mtx-textarea" placeholder="catatan transaksi"></textarea>
            </div>

            <button type="submit" class="mtx-btn mtx-btn-primary" :disabled="form.processing">
              <span v-if="form.processing">{{ $t('common.saving') }}</span>
              <span v-else>{{ $t('common.save') }}</span>
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

export default {
  layout: LayoutApp,
  components: { Head, Link },
  props: { accounts: Array, counterAccounts: Array },
  setup() {
    const form = useForm({
      transaction_date: new Date().toISOString().slice(0, 10),
      type: 'in',
      account_id: '',
      counter_account_id: '',
      amount: '',
      reference_no: '',
      description: '',
    });

    const submit = () => {
      form.post('/apps/cash-bank', {
        preserveScroll: true,
        onSuccess: () => form.reset('amount', 'reference_no', 'description'),
      });
    };

    return { form, submit };
  },
};
</script>