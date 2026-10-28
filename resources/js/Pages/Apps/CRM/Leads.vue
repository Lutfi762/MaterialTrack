<template>
    <Head><title>CRM Leads - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-7xl mx-auto flex gap-6">
            <!-- Form -->
            <div class="w-1/3">
                <div class="mtx-card">
                    <div class="mtx-card-header"><span class="mtx-card-title">New Lead</span></div>
                    <div class="mtx-card-body">
                        <form @submit.prevent="submit">
                            <div class="mtx-field"><label class="mtx-label">Name *</label><input v-model="form.name" class="mtx-input" required></div>
                            <div class="mtx-field"><label class="mtx-label">Company</label><input v-model="form.company" class="mtx-input"></div>
                            <div class="mtx-field"><label class="mtx-label">Email</label><input type="email" v-model="form.email" class="mtx-input"></div>
                            <div class="mtx-field"><label class="mtx-label">Phone</label><input v-model="form.phone" class="mtx-input"></div>
                            <div class="mtx-field"><label class="mtx-label">Status</label><select v-model="form.status" class="mtx-input"><option value="new">New</option><option value="contacted">Contacted</option><option value="qualified">Qualified</option><option value="converted">Converted</option><option value="lost">Lost</option></select></div>
                            <button type="submit" class="mtx-btn mtx-btn-primary w-full mt-4">Save Lead</button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- List -->
            <div class="w-2/3 mtx-card p-4">
                <div class="font-bold text-lg mb-4">Leads Directory</div>
                <div class="space-y-3">
                    <div v-for="l in leads.data" :key="l.id" class="p-3 border rounded flex justify-between items-center hover:bg-gray-50">
                        <div>
                            <div class="font-bold">{{ l.name }} <span class="text-xs font-normal text-gray-500">({{ l.company || 'Individu' }})</span></div>
                            <div class="text-xs text-gray-600 mt-1"><i class="fa fa-envelope mr-1"></i>{{ l.email || '-' }} <i class="fa fa-phone ml-3 mr-1"></i>{{ l.phone || '-' }}</div>
                        </div>
                        <select :value="l.status" @change="updateStatus(l.id, $event.target.value)" class="text-xs rounded border-gray-300">
                            <option value="new">New</option><option value="contacted">Contacted</option><option value="qualified">Qualified</option><option value="converted">Converted</option><option value="lost">Lost</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>
<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
export default {
    layout: LayoutApp, components: { Head }, props: { leads: Object, users: Array },
    setup() {
        const form = useForm({ name: '', company: '', email: '', phone: '', status: 'new' });
        const submit = () => form.post('/apps/crm/leads', { onSuccess: () => form.reset() });
        const updateStatus = (id, status) => router.put(`/apps/crm/leads/${id}`, { status });
        return { form, submit, updateStatus };
    }
}
</script>
