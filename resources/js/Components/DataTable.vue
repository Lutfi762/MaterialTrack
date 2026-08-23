<template>
  <div class="w-full overflow-x-auto">
    <div class="relative">
      <table class="min-w-full divide-y divide-gray-200" :class="{ 'table-fixed': fixedLayout }">
        <thead class="bg-gray-50">
          <tr>
            <template v-for="col in columns" :key="col.key">
              <th
                :class="[
                  'px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider',
                  col.align === 'right' ? 'text-right' : 'text-left',
                  col.align === 'center' ? 'text-center' : '',
                  col.width ? '' : 'min-w-0'
                ]"
                :style="{ width: col.width, minWidth: col.minWidth }"
                v-if="!col.hideInHeader"
              >
                <div class="flex items-center gap-1" v-if="col.sortable">
                  <span>{{ col.label }}</span>
                  <button
                    type="button"
                    @click="handleSort(col.key)"
                    class="p-0.5 hover:bg-gray-200 rounded transition-colors"
                    :aria-label="'Sort by ' + col.label"
                    :aria-sort="sortBy === col.key ? (sortDir === 'asc' ? 'ascending' : 'descending') : 'none'"
                  >
                    <svg
                      v-if="sortBy === col.key"
                      :class="sortDir === 'asc' ? 'rotate-180' : ''"
                      class="w-4 h-4 text-gray-400"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                    <svg
                      v-else
                      class="w-4 h-4 text-gray-300"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    </svg>
                  </button>
                </div>
                <span v-else>{{ col.label }}</span>
              </th>
            </template>
          </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="loading" class="animate-pulse">
            <td :colspan="visibleColumns.length" class="px-4 py-12 text-center">
              <div class="flex flex-col items-center gap-3 text-gray-500">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                </svg>
                <span>{{ loadingText || $t('common.loading') }}</span>
              </div>
            </td>
          </tr>

          <tr v-else-if="!loading && (!rows || rows.length === 0)">
            <td :colspan="visibleColumns.length" class="px-4 py-12 text-center">
              <div class="flex flex-col items-center gap-3 text-gray-500">
                <svg v-if="emptyIcon" :class="emptyIcon" class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <svg v-else class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-sm">{{ emptyText || $t('common.no_data') }}</span>
              </div>
            </td>
          </tr>

          <template v-else>
            <tr
              v-for="(row, rowIndex) in rows"
              :key="getRowKey(row, rowIndex)"
              :class="[
                'hover:bg-gray-50 transition-colors duration-100',
                rowClass ? (typeof rowClass === 'function' ? rowClass(row, rowIndex) : rowClass) : '',
                striped && rowIndex % 2 === 1 ? 'bg-gray-50' : ''
              ]"
              @click="rowClick && $emit('row-click', row, rowIndex)"
            >
              <template v-for="col in visibleColumns" :key="col.key">
                <td
                  :class="[
                    'px-4 py-3 text-sm',
                    col.align === 'right' ? 'text-right' : 'text-left',
                    col.align === 'center' ? 'text-center' : '',
                    col.className || '',
                    'whitespace-nowrap'
                  ]"
                  :style="{ width: col.width, minWidth: col.minWidth }"
                >
                                    <slot
                    :name="'cell-' + String(col.key)"
                    :row="row"
                    :index="rowIndex"
                    :value="getCellValue(row, col, rowIndex)"
                  >
                    <template v-if="!col.render">
                      <span v-if="isBoolean(getCellValue(row, col, rowIndex))" :class="getCellValue(row, col, rowIndex) ? 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800' : 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800'">
                        {{ getCellValue(row, col, rowIndex) ? $t('common.yes') : $t('common.no') }}
                      </span>
                      <span v-else-if="isDate(getCellValue(row, col, rowIndex))" class="font-mono">
                        {{ formatDate(getCellValue(row, col, rowIndex)) }}
                      </span>
                      <span v-else-if="isNumber(getCellValue(row, col, rowIndex)) && col.format === 'currency'" class="font-mono">
                        {{ formatCurrency(getCellValue(row, col, rowIndex)) }}
                      </span>
                      <span v-else-if="isNumber(getCellValue(row, col, rowIndex)) && col.format === 'number'" class="font-mono">
                        {{ formatNumber(getCellValue(row, col, rowIndex)) }}
                      </span>
                      <span v-else class="break-all">{{ getCellValue(row, col, rowIndex) ?? '-' }}</span>
                    </template>
                  </slot>
                </td>
              </template>
            </tr>
          </template>
        </tbody>

        <tfoot v-if="showFooter && footerRows && footerRows.length > 0" class="bg-gray-50">
          <tr v-for="(footerRow, idx) in footerRows" :key="idx">
            <template v-for="col in visibleColumns" :key="col.key">
              <td
                :class="[
                  'px-4 py-3 text-sm font-semibold',
                  col.align === 'right' ? 'text-right' : 'text-left',
                  col.align === 'center' ? 'text-center' : ''
                ]"
                :colspan="footerRow.span || 1"
                v-if="footerRow.key === col.key || (!footerRow.key && idx === 0 && col.key === visibleColumns[0].key)"
              >
                {{ footerRow.label || footerRow.value || '' }}
              </td>
            </template>
          </tr>
        </tfoot>
      </table>

      <div v-if="stickyHeader" class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-b from-white to-transparent pointer-events-none" aria-hidden="true" />
    </div>

    <div v-if="showPagination && pagination" class="mt-4 flex items-center justify-between">
      <slot name="pagination" :pagination="pagination">
        <Pagination :links="pagination.links" :align="paginationAlign" />
      </slot>
    </div>
    </div>
</template>

<script>
import Pagination from './Pagination.vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

export default {
  name: 'DataTable',
  components: { Pagination },
  props: {
    columns: {
      type: Array,
      required: true,
    },
    rows: {
      type: Array,
      default: () => [],
    },
    rowKey: {
      type: [String, Function],
      default: 'id',
    },
    loading: {
      type: Boolean,
      default: false,
    },
    loadingText: {
      type: String,
      default: '',
    },
    emptyText: {
      type: String,
      default: '',
    },
    emptyIcon: {
      type: String,
      default: '',
    },
    striped: {
      type: Boolean,
      default: true,
    },
    fixedLayout: {
      type: Boolean,
      default: false,
    },
    stickyHeader: {
      type: Boolean,
      default: false,
    },
    rowClass: {
      type: [String, Function],
      default: '',
    },
    rowClick: {
      type: Boolean,
      default: false,
    },
    pagination: {
      type: Object,
      default: null,
    },
    showPagination: {
      type: Boolean,
      default: true,
    },
    paginationAlign: {
      type: String,
      default: 'end',
    },
    showFooter: {
      type: Boolean,
      default: false,
    },
    footerRows: {
      type: Array,
      default: () => [],
    },
    sortBy: {
      type: String,
      default: '',
    },
    sortDir: {
      type: String,
      default: 'asc',
    },
  },
  emits: ['sort', 'row-click', 'update:sortBy', 'update:sortDir'],
  setup(props, { emit }) {
    const { t } = useI18n();

    const visibleColumns = computed(() =>
      props.columns.filter(c => !c.hideInHeader)
    );

    const getRowKey = (row, index) => {
      if (typeof props.rowKey === 'function') {
        return props.rowKey(row, index);
      }
      return row[props.rowKey] ?? `row-${index}`;
    };

    const getCellValue = (row, col, index) => {
      if (typeof col.key === 'function') {
        return col.key(row, index);
      }
      const keys = col.key.split('.');
      let value = row;
      for (const k of keys) {
        if (value === null || value === undefined) return null;
        value = value[k];
      }
      return value;
    };

    const handleSort = (key) => {
      if (!props.columns.find(c => c.key === key)?.sortable) return;
      let newDir = 'asc';
      if (props.sortBy === key && props.sortDir === 'asc') {
        newDir = 'desc';
      }
      emit('update:sortBy', key);
      emit('update:sortDir', newDir);
      emit('sort', { key, dir: newDir });
    };

    const isBoolean = (val) => typeof val === 'boolean';
    const isDate = (val) => val instanceof Date || (typeof val === 'string' && /^\d{4}-\d{2}-\d{2}/.test(val));
    const isNumber = (val) => typeof val === 'number' && !isNaN(val);

    const formatDate = (val) => {
      if (!val) return '-';
      const d = val instanceof Date ? val : new Date(val);
      return d.toLocaleDateString('id-ID');
    };

    const formatCurrency = (val) => {
      if (val === null || val === undefined) return '-';
      return 'Rp ' + Number(val).toLocaleString('id-ID');
    };

    const formatNumber = (val) => {
      if (val === null || val === undefined) return '-';
      return Number(val).toLocaleString('id-ID');
    };

    return {
      t,
      visibleColumns,
      getRowKey,
      getCellValue,
      handleSort,
      isBoolean,
      isDate,
      isNumber,
      formatDate,
      formatCurrency,
      formatNumber,
    };
  },
};
</script>

<style scoped>
td, th {
  overflow: hidden;
  text-overflow: ellipsis;
}

@media (max-width: 640px) {
  .w-full.overflow-x-auto {
    margin-left: -1rem;
    margin-right: -1rem;
    padding-left: 1rem;
    padding-right: 1rem;
  }
}
</style>
