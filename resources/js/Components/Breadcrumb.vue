<template>
    <nav class="breadcrumb-modern mb-3">
        <Link href="/apps/dashboard" class="bc-item">Dashboard</Link>
        <i class="fa fa-chevron-right bc-sep"></i>
        <span v-for="(item, i) in items" :key="i">
            <Link v-if="item.href" :href="item.href" class="bc-item">{{ item.label }}</Link>
            <span v-else class="bc-current">{{ item.label }}</span>
            <i v-if="i < items.length - 1" class="fa fa-chevron-right bc-sep"></i>
        </span>
    </nav>
</template>
<script setup>
import { Link } from '@inertiajs/vue3'
defineProps({ items: Array })
</script>
<style scoped>
.breadcrumb-modern { display: flex; align-items: center; gap: 8px; font-size: 12px; }
.bc-item { color: #64748b; text-decoration: none; }
.bc-item:hover { color: #3b82f6; }
.bc-current { color: #1e293b; font-weight: 600; }
.bc-sep { font-size: 8px; color: #cbd5e1; }
</style>