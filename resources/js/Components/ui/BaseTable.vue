<script setup lang="ts">
export interface TableColumn {
  key: string;
  label: string;
}

type TableCell = string | number | null | undefined;
type TableRow = Record<string, TableCell>;

withDefaults(defineProps<{
  columns: TableColumn[];
  rows: TableRow[];
  emptyMessage?: string;
}>(), {
  emptyMessage: 'No hay registros para mostrar.',
});
</script>

<template>
  <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-800">
    <table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-800">
      <thead class="bg-slate-50 dark:bg-slate-800/70">
        <tr>
          <th v-for="column in columns" :key="column.key" scope="col" class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-200">{{ column.label }}</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-900">
        <tr v-if="rows.length === 0">
          <td :colspan="columns.length" class="px-4 py-8 text-center text-slate-500">{{ emptyMessage }}</td>
        </tr>
        <tr v-for="(row, index) in rows" :key="index" class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
          <td v-for="column in columns" :key="column.key" class="px-4 py-3 text-slate-700 dark:text-slate-200">
            <slot :name="`cell-${column.key}`" :row="row">{{ row[column.key] ?? '—' }}</slot>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
