<template>
    <nav v-if="links.length > 3" class="pagination-modern-wrapper">
        <ul :class="`pagination justify-content-${align} mb-0`">
            <li
                v-for="(link, index) in links"
                :key="index"
                :class="[
                    'page-item',
                    link.url == null ? 'disabled' : '',
                    link.active ? 'active' : '',
                ]"
            >
                <Link
                    class="page-link"
                    :href="link.url === null ? '#' : link.url"
                    v-html="link.label"
                />
            </li>
        </ul>
    </nav>
</template>

<script>
import { Link } from '@inertiajs/vue3';

export default {
    components: { Link },
    props: {
        links: Array,
        align: {
            type: String,
            default: 'end',
        },
    },
}
</script>

<style scoped>
.pagination-modern-wrapper {
    margin-top: 20px;
}
.pagination-modern-wrapper :deep(.page-link) {
    border-radius: 8px;
    margin: 0 3px;
    border: 1.5px solid #e5e9f0;
    color: #495057;
    font-weight: 500;
    font-size: 13px;
    padding: 8px 14px;
}
.pagination-modern-wrapper :deep(.page-item.active .page-link) {
    background: #3b82f6;
    border-color: #3b82f6;
    box-shadow: 0 3px 8px rgba(59, 130, 246, 0.3);
}
.pagination-modern-wrapper :deep(.page-item.disabled .page-link) {
    color: #cbd5e1;
    border-color: #eef1f6;
}
.pagination-modern-wrapper :deep(.page-link:hover:not(.disabled)) {
    background: #eef4ff;
    border-color: #3b82f6;
}
</style>