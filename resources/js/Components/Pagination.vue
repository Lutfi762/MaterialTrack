<template>
  <nav v-if="links && links.length > 2" :aria-label="paginationAriaLabel" class="flex items-center justify-center gap-1">
    <button
      @click="goToPage(prevLink.url)"
      :disabled="!prevLink.url"
      class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
      :aria-label="prevLabel"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
      </svg>
    </button>

    <template v-for="link in pageLinks" :key="link.url || link.label">
      <span v-if="link.active" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700 cursor-default" aria-current="page">
        {{ link.label }}
      </span>
      <span v-else-if="link.label === '...'" class="px-2 py-1.5 text-sm text-gray-400" aria-hidden="true">…</span>
      <button
        v-else
        @click="goToPage(link.url)"
        :disabled="!link.url"
        class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
        :aria-label="pageLabel + ' ' + link.label"
      >
        {{ link.label }}
      </button>
    </template>

    <button
      @click="goToPage(nextLink.url)"
      :disabled="!nextLink.url"
      class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
      :aria-label="nextLabel"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </button>
  </nav>
</template>

<script>
import { router } from '@inertiajs/vue3';

export default {
  name: 'Pagination',
  props: {
    links: {
      type: Array,
      required: true,
    },
    align: {
      type: String,
      default: 'end',
    },
    prevLabel: {
      type: String,
      default: 'Previous',
    },
    nextLabel: {
      type: String,
      default: 'Next',
    },
    pageLabel: {
      type: String,
      default: 'Page',
    },
    paginationAriaLabel: {
      type: String,
      default: 'Pagination',
    },
  },
  emits: ['page-change'],
  computed: {
    // Laravel paginator links(): [prev, ...pages, next] — label datang sbg
    // HTML entity mentah ('&laquo; Previous' / 'Next &raquo;'), jadi dipakai
    // hanya utk deteksi posisi, bukan ditampilkan (icon SVG dipakai instead).
    prevLink() {
      return this.links[0] ?? { url: null };
    },
    nextLink() {
      return this.links[this.links.length - 1] ?? { url: null };
    },
    pageLinks() {
      return this.links.slice(1, -1);
    },
  },
  methods: {
    goToPage(url) {
      if (!url) return;
      // Emit utk parent yang mau handle manual; jika tak ada listener,
      // navigasi sendiri via Inertia (perbaikan: sebelumnya emit tak
      // pernah didengarkan sehingga klik nomor halaman tidak berefek).
      this.$emit('page-change', url);
      if (this.$attrs.onPageChange === undefined) {
        router.get(url, {}, { preserveState: true, preserveScroll: true, replace: true });
      }
    },
  },
};
</script>

<style>
/* Empty — inline utility classes used instead of @apply */
</style>
