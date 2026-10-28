<template>
    <Head><title>Opportunities (Pipeline) - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Sales Pipeline</h1>
            <button @click="showModal=true" class="mtx-btn mtx-btn-primary"><i class="fa fa-plus mr-2"></i>New Opportunity</button>
        </div>
        
        <!-- Kanban Board -->
        <div class="flex gap-4 overflow-x-auto pb-4">
            <div v-for="stage in stages" :key="stage.key" class="w-80 shrink-0 bg-gray-100 rounded-lg p-3">
                <div class="font-bold mb-3 flex justify-between items-center">
                    <span class="uppercase text-gray-600 text-sm">{{ stage.label }}</span>
                    <span class="bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full">{{ getOppsByStage(stage.key).length }}</span>
                </div>
                <div class="space-y-3">
                    <div v-for="opp in getOppsByStage(stage.key)" :key="opp.id" class="bg-white p-3 rounded shadow-sm border border-gray-200">
                        <div class="font-bold text-sm text-blue-700">{{ opp.title }}</div>
                        <div class="text-xs text-gray-500 mt-1">{{ opp.customer?.name || opp.lead?.name || 'No Contact' }}</div>
                        <div class="mt-2 flex justify-between items-center">
                            <span class="text-xs font-bold text-green-600">Rp {{ Number(opp.expected_revenue).toLocaleString('id-ID') }}</span>
                        </div>
                        <div class="mt-2 text-right">
                            <select :value="opp.stage" @change="updateStage(opp.id, $event.target.value)" class="text-xs py-0 pl-2 pr-6 h-6 rounded border-gray-300">
                                <option v-for="s in stages" :key="s.key" :value="s.key">{{ s.label }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal Fallback (Simple form below board for caveat) -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-lg">
                <h3 class="font-bold text-lg mb-4">Add Opportunity</h3>
                <form @submit.prevent="submit">
                    <div class="mtx-field"><label class="mtx-label">Title *</label><input v-model="form.title" class="mtx-input" required></div>
                    <div class="mtx-field"><label class="mtx-label">Expected Revenue *</label><input type="number" v-model="form.expected_revenue" class="mtx-input" min="0" required></div>
                    <div class="mtx-field"><label class="mtx-label">Link to Lead</label>
                        <select v-model="form.crm_lead_id" class="mtx-input"><option value="">-- None --</option><option v-for="l in leads" :key="l.id" :value="l.id">{{ l.name }}</option></select>
                    </div>
                    <div class="mtx-field"><label class="mtx-label">Stage</label>
                        <select v-model="form.stage" class="mtx-input"><option v-for="s in stages" :key="s.key" :value="s.key">{{ s.label }}</option></select>
                    </div>
                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" @click="showModal=false" class="mtx-btn mtx-btn-secondary">Cancel</button>
                        <button type="submit" class="mtx-btn mtx-btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</template>
<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
export default {
    layout: LayoutApp, components: { Head }, props: { opportunities: Array, leads: Array, customers: Array },
    setup(props) {
        const stages = [
            { key: 'prospecting', label: 'Prospecting' },
            { key: 'proposal', label: 'Proposal' },
            { key: 'negotiation', label: 'Negotiation' },
            { key: 'closed_won', label: 'Closed Won' },
            { key: 'closed_lost', label: 'Closed Lost' }
        ];
        const getOppsByStage = (s) => props.opportunities.filter(o => o.stage === s);
        
        const showModal = ref(false);
        const form = useForm({ title: '', expected_revenue: 0, crm_lead_id: '', stage: 'prospecting' });
        const submit = () => form.post('/apps/crm/opportunities', { onSuccess: () => { showModal.value = false; form.reset(); } });
        const updateStage = (id, stage) => router.put(`/apps/crm/opportunities/${id}`, { stage });
        
        return { stages, getOppsByStage, showModal, form, submit, updateStage };
    }
}
</script>
