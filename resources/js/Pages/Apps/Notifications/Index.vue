<template>
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Notifikasi</h2>
        <div class="flex gap-2">
          <button @click="refresh" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm font-medium">
            &#8635; Perbarui
          </button>
          <button v-if="unreadCount > 0" @click="markAll"
                  class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
            Tandai Semua Dibaca
          </button>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div v-if="notifications.data.length === 0"
             class="bg-white rounded-lg shadow p-10 text-center text-gray-400 text-sm">
          Belum ada notifikasi.
        </div>

        <div v-else class="space-y-3">
          <div v-for="n in notifications.data" :key="n.id"
               class="bg-white rounded-lg shadow-sm p-4 border"
               :class="n.is_read ? 'border-gray-100' : 'border-indigo-200'">
            <div class="flex justify-between gap-3">
              <div class="flex-1 cursor-pointer" @click="n.link && (window.location.href = n.link)">
                <div class="text-sm font-semibold" :class="n.is_read ? 'text-gray-700' : 'text-gray-900'">{{ n.title }}</div>
                <div class="text-sm text-gray-600 mt-0.5">{{ n.message }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ new Date(n.created_at).toLocaleString('id-ID') }}</div>
              </div>
              <button v-if="!n.is_read" @click="markRead(n.id)"
                      class="text-xs text-indigo-600 font-medium whitespace-nowrap">Tandai dibaca</button>
            </div>
          </div>

          <div v-if="notifications.last_page > 1" class="flex justify-between items-center mt-4">
            <button v-if="notifications.prev_page_url" @click="go(notifications.current_page - 1)"
                    class="px-3 py-2 bg-gray-200 text-gray-700 rounded-md text-sm">Sebelumnya</button>
            <span v-else class="px-3 py-2 text-gray-300 text-sm">Sebelumnya</span>
            <span class="text-sm text-gray-500">Halaman {{ notifications.current_page }} / {{ notifications.last_page }}</span>
            <button v-if="notifications.next_page_url" @click="go(notifications.current_page + 1)"
                    class="px-3 py-2 bg-gray-200 text-gray-700 rounded-md text-sm">Berikutnya</button>
            <span v-else class="px-3 py-2 text-gray-300 text-sm">Berikutnya</span>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/App.vue';

const csrfHeaders = {
  'Accept': 'application/json',
  'X-Requested-With': 'XMLHttpRequest',
  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
};

export default {
  components: { AppLayout },
  props: { notifications: Object },
  computed: {
    unreadCount() {
      return this.notifications.data.filter((n) => !n.is_read).length;
    },
  },
  methods: {
    go(page) {
      this.$inertia.get(route('apps.notifications.all'), { page }, {
        preserveState: true, replace: true, preserveScroll: true,
      });
    },
    async markRead(id) {
      await fetch(route('apps.notifications.mark-read', id), { method: 'POST', headers: csrfHeaders });
      this.$inertia.reload({ only: ['notifications'] });
    },
    async markAll() {
      await fetch(route('apps.notifications.mark-all-read'), { method: 'POST', headers: csrfHeaders });
      this.$inertia.reload({ only: ['notifications'] });
    },
    async refresh() {
      await fetch(route('apps.notifications.refresh'), { method: 'POST', headers: csrfHeaders });
      this.$inertia.reload({ only: ['notifications'] });
    },
  },
};
</script>