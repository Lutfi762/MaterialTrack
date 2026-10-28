<template>
    <Head><title>Data PIC - MaterialTrack</title></Head>
    <main class="p-6">
        <div class="max-w-7xl mx-auto">
            <div class="mtx-page-header">
                <div>
                    <h1 class="mtx-page-title"><i class="fa fa-id-badge text-indigo-600" aria-hidden="true"></i> {{ $t('pics.heading') }}</h1>
                    <p class="mtx-hint">{{ $t('pics.subtitle') }}</p>
                </div>
                <Link v-if="hasAnyPermission(['pics.create'])" href="/apps/pics/create" class="mtx-btn mtx-btn-primary">
                    <i class="fa fa-plus" aria-hidden="true"></i> {{ $t('pics.add') }}
                </Link>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4" v-if="pics.data.length > 0">
                <div class="mtx-card" v-for="pic in pics.data" :key="pic.id">
                    <div class="mtx-card-header">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 shrink-0 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-sm">{{ initials(pic.name) }}</div>
                            <div>
                                <div class="font-semibold text-gray-900">{{ pic.name }}</div>
                                <span class="mtx-badge mtx-badge-info">{{ pic.department?.name ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mtx-card-body space-y-2">
                        <div class="flex items-center gap-2 text-sm text-gray-600" v-if="pic.nik">
                            <i class="fa fa-id-card text-gray-400" aria-hidden="true"></i> {{ pic.nik }}
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-600" v-if="pic.phone">
                            <i class="fa fa-phone text-gray-400" aria-hidden="true"></i> {{ pic.phone }}
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-600" v-if="pic.email">
                            <i class="fa fa-envelope text-gray-400" aria-hidden="true"></i> {{ pic.email }}
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-400" v-if="!pic.nik && !pic.phone && !pic.email">
                            <i class="fa fa-circle-info" aria-hidden="true"></i> {{ $t('pics.empty_contact') }}
                        </div>
                        <div class="flex gap-2 pt-2 border-t border-gray-100 mt-3">
                            <Link v-if="hasAnyPermission(['pics.edit'])" :href="`/apps/pics/${pic.id}/edit`" class="mtx-btn mtx-btn-secondary mtx-btn-sm flex-1">
                                <i class="fa fa-pencil" aria-hidden="true"></i> {{ $t('common.edit') }}
                            </Link>
                            <button v-if="hasAnyPermission(['pics.delete'])" class="mtx-btn mtx-btn-danger mtx-btn-sm flex-1" @click="handleDelete(pic.id)">
                                <i class="fa fa-trash" aria-hidden="true"></i> {{ $t('common.delete') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mtx-card text-center py-12" v-else>
                <i class="fa fa-id-badge text-4xl text-gray-300" aria-hidden="true"></i>
                <p class="mt-3 mb-0 font-semibold text-gray-700">{{ $t('pics.empty') }}</p>
                <p class="text-sm text-gray-400">{{ $t('pics.add_desc') }}</p>
            </div>

            <Pagination v-if="pics.data.length > 0" :links="pics.links" align="center" />
        </div>
    </main>
</template>

<script>
import LayoutApp from '../../../Layouts/App.vue';
import Pagination from '../../../Components/Pagination.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { confirmDelete } from '@/Utils/confirmDialog';

export default {
    layout: LayoutApp,
    components: { Head, Link, Pagination },
    props: { pics: Object },
    setup() {
        const isLoading = ref(false);

        const initials = (name) => {
            if (!name) return '?';
            const parts = name.trim().split(' ');
            return parts.length > 1
                ? (parts[0][0] + parts[1][0]).toUpperCase()
                : parts[0].substring(0, 2).toUpperCase();
        };

        const handleDelete = async (id) => {
            if (await confirmDelete($t('pics.delete_confirm'))) {
                isLoading.value = true;
                router.delete(`/apps/pics/${id}`, {
                    onFinish: () => { isLoading.value = false; },
                    onError: () => {
                        isLoading.value = false;
                    }
                });
            }
        };

        return { initials, handleDelete, isLoading };
    },
};
</script>

