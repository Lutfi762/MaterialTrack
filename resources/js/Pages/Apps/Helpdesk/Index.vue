<template>
  <Head><title>Helpdesk - MaterialTrack</title></Head>
  <main class="p-6"><div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
    <section class="mtx-card"><div class="mtx-card-header"><span class="mtx-card-title">New Ticket</span></div><div class="mtx-card-body"><form @submit.prevent="submit">
      <div class="mtx-field"><label class="mtx-label">Subject *</label><input v-model="form.subject" class="mtx-input" required></div>
      <div class="mtx-field"><label class="mtx-label">Description *</label><textarea v-model="form.description" class="mtx-textarea" required></textarea></div>
      <div class="mtx-field"><label class="mtx-label">Priority</label><select v-model="form.priority" class="mtx-input"><option value="low">Low</option><option value="medium">Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
      <div class="mtx-field"><label class="mtx-label">Department</label><select v-model="form.department_id" class="mtx-input"><option value="">Unassigned</option><option v-for="department in departments" :key="department.id" :value="department.id">{{ department.name }}</option></select></div>
      <button class="mtx-btn mtx-btn-primary w-full" :disabled="form.processing">Create Ticket</button>
    </form></div></section>
    <section class="lg:col-span-2 mtx-card"><div class="mtx-card-header"><span class="mtx-card-title">Tickets</span></div><div class="overflow-x-auto"><table class="min-w-full text-sm"><thead><tr class="border-b"><th class="p-3 text-left">Ticket</th><th class="p-3 text-left">Priority</th><th class="p-3 text-left">Status</th><th class="p-3"></th></tr></thead><tbody><tr v-for="ticket in tickets.data" :key="ticket.id" class="border-b"><td class="p-3"><b>{{ ticket.ticket_number }}</b><br><span>{{ ticket.subject }}</span></td><td class="p-3">{{ ticket.priority }}</td><td class="p-3">{{ ticket.status.replace('_', ' ') }}</td><td class="p-3"><Link :href="`/apps/helpdesk/${ticket.id}`" class="text-primary">Open</Link></td></tr><tr v-if="!tickets.data.length"><td colspan="4" class="p-6 text-center text-gray-500">No tickets.</td></tr></tbody></table></div></section>
  </div></main>
</template>
<script>
import LayoutApp from '@/Layouts/App.vue'; import { Head, Link, useForm } from '@inertiajs/vue3';
export default { layout: LayoutApp, components: { Head, Link }, props: { tickets:Object, departments:Array }, setup() { const form=useForm({subject:'',description:'',priority:'medium',department_id:''}); return {form,submit:()=>form.post('/apps/helpdesk',{onSuccess:()=>form.reset()})}; } };
</script>
