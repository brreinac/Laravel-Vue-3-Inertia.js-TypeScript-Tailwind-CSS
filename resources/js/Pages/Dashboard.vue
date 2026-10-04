<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import AppShell from '../Components/AppShell.vue';
import StatsChart from '../Components/StatsChart.vue';
import { api, unwrapCollection } from '../lib/api';
import type { DashboardResponse, Task, TaskStatus } from '../types';

const statistics = reactive<Record<TaskStatus, number>>({ pendiente: 0, en_progreso: 0, en_revision: 0, completada: 0 });
const upcoming = ref<Task[]>([]);
const loading = ref(true);
const error = ref('');

const cards: Array<{ key: TaskStatus; label: string; class: string }> = [
  { key: 'pendiente', label: 'Pendientes', class: 'border-slate-300' },
  { key: 'en_progreso', label: 'En progreso', class: 'border-blue-300' },
  { key: 'en_revision', label: 'En revisión', class: 'border-amber-300' },
  { key: 'completada', label: 'Completadas', class: 'border-emerald-300' },
];

onMounted(async () => {
  try {
    const data = await api<DashboardResponse>('/api/dashboard/stats');
    Object.assign(statistics, data.statistics);
    upcoming.value = unwrapCollection(data.upcoming_tasks);
  } catch {
    error.value = 'No se pudo cargar el dashboard.';
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <AppShell>
    <div class="flex items-end justify-between gap-4"><div><h1 class="page-title">Dashboard</h1><p class="muted mt-1">Una vista rápida del trabajo que requiere atención.</p></div></div>
    <p v-if="error" class="mt-6 rounded-lg bg-rose-50 p-4 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300" role="alert">{{ error }}</p>
    <div v-else-if="loading" class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4"><div v-for="item in cards" :key="item.key" class="h-28 animate-pulse rounded-xl bg-slate-200 dark:bg-slate-800" /></div>
    <template v-else>
      <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4"><article v-for="card in cards" :key="card.key" class="surface border-l-4 p-5" :class="card.class"><p class="muted">{{ card.label }}</p><p class="mt-2 text-3xl font-bold">{{ statistics[card.key] }}</p></article></div>
      <div class="mt-6 grid gap-6 lg:grid-cols-2"><section class="surface p-5"><h2 class="font-bold">Distribución por estado</h2><StatsChart class="mt-4" :data="statistics" /></section><section class="surface p-5"><h2 class="font-bold">Próximas a vencer</h2><ul v-if="upcoming.length" class="mt-4 divide-y divide-slate-100 dark:divide-slate-800"><li v-for="task in upcoming" :key="task.id" class="py-3"><p class="font-medium">{{ task.title }}</p><p class="muted">{{ task.project?.name }} · Vence {{ task.due_date }}</p></li></ul><p v-else class="mt-4 muted">No hay tareas próximas a vencer.</p></section></div>
    </template>
  </AppShell>
</template>
