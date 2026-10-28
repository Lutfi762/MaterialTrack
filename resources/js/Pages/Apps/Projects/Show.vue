<template>
    <Head><title>{{ project.code }} - MaterialTrack</title></Head>
    <main class="p-6 flex gap-6 max-w-7xl mx-auto">
        <!-- Project Sidebar -->
        <div class="w-1/3 space-y-4">
            <div class="mtx-card p-4">
                <h3 class="font-bold text-lg mb-2">{{ project.name }}</h3>
                <p class="text-sm text-gray-600 mb-4">{{ project.code }}</p>
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase">Status</label>
                    <select v-model="statusForm.status" @change="updateStatus" class="mtx-input mt-1">
                        <option value="planning">Planning</option><option value="in_progress">In Progress</option><option value="on_hold">On Hold</option><option value="completed">Completed</option>
                    </select>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Start Date:</span><span>{{ project.start_date }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">End Date:</span><span>{{ project.end_date || '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Budget:</span><span class="font-bold">Rp {{ Number(project.budget).toLocaleString('id-ID') }}</span></div>
                </div>
            </div>
        </div>
        
        <!-- Task Board -->
        <div class="w-2/3 mtx-card p-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg">Tasks</h3>
                <button @click="showTaskForm=true" v-if="!showTaskForm" class="mtx-btn mtx-btn-primary mtx-btn-sm">+ Add Task</button>
            </div>
            
            <form v-if="showTaskForm" @submit.prevent="addTask" class="mb-6 p-4 border rounded bg-gray-50">
                <div class="flex gap-2 mb-2">
                    <input v-model="taskForm.name" class="mtx-input flex-1" placeholder="Task Name..." required>
                    <input type="date" v-model="taskForm.due_date" class="mtx-input w-40">
                </div>
                <div class="flex gap-2 items-center">
                    <select v-model="taskForm.assigned_to" class="mtx-input w-48"><option value="">Unassigned</option><option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option></select>
                    <button type="submit" class="mtx-btn mtx-btn-primary mtx-btn-sm" :disabled="taskForm.processing">Save</button>
                    <button type="button" @click="showTaskForm=false" class="mtx-btn mtx-btn-secondary mtx-btn-sm">Cancel</button>
                </div>
            </form>

            <div class="space-y-2">
                <div v-for="task in project.tasks" :key="task.id" class="p-3 border rounded flex justify-between items-center hover:bg-gray-50">
                    <div>
                        <div class="font-semibold" :class="{'line-through text-gray-400': task.status==='done'}">{{ task.name }}</div>
                        <div class="text-xs text-gray-500 mt-1">
                            <span v-if="task.assignee"><i class="fa fa-user mr-1"></i>{{ task.assignee.name }}</span>
                            <span v-if="task.due_date" class="ml-3"><i class="fa fa-calendar mr-1"></i>{{ task.due_date }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <select :value="task.status" @change="updateTaskStatus(task.id, $event.target.value)" class="text-xs border-gray-300 rounded focus:ring-0">
                            <option value="todo">To Do</option><option value="in_progress">In Progress</option><option value="done">Done</option>
                        </select>
                        <button @click="deleteTask(task.id)" class="text-red-500 hover:text-red-700 text-sm"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
                <div v-if="project.tasks.length===0" class="text-center text-gray-500 py-4 text-sm">No tasks added yet.</div>
            </div>
        </div>
    </main>
</template>
<script>
import LayoutApp from '@/Layouts/App.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

export default {
    layout: LayoutApp, components: { Head }, props: { project: Object, users: Array },
    setup(props) {
        const statusForm = useForm({ status: props.project.status });
        const updateStatus = () => statusForm.put(`/apps/projects/${props.project.id}`);
        
        const showTaskForm = ref(false);
        const taskForm = useForm({ name: '', assigned_to: '', due_date: '' });
        
        const addTask = () => {
            taskForm.post(`/apps/projects/${props.project.id}/tasks`, {
                onSuccess: () => { taskForm.reset(); showTaskForm.value = false; }
            });
        };
        const updateTaskStatus = (taskId, status) => router.put(`/apps/tasks/${taskId}`, { status });
        const deleteTask = (taskId) => { if(confirm('Delete task?')) router.delete(`/apps/tasks/${taskId}`); };
        
        return { statusForm, updateStatus, showTaskForm, taskForm, addTask, updateTaskStatus, deleteTask };
    }
}
</script>
