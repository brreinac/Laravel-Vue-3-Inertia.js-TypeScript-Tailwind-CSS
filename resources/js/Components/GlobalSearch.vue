<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { api, unwrapCollection } from '../lib/api';
import { useDebounce } from '../composables/useDebounce';
import type { SearchResults } from '../types';

const query = ref('');
const results = ref<SearchResults | null>(null);
const loading = ref(false);
const debounce = useDebounce();
const hasResults = computed(() => results.value !== null && [
  ...unwrapCollection(results.value.projects),
  ...unwrapCollection(results.value.tasks),
  ...unwrapCollection(results.value.users),
].length > 0);

const search = async (): Promise<void> => {
  if (query.value.trim().length < 2) {
    results.value = null;
    return;
  }

  loading.value = true;
  try {
    results.value = await api<SearchResults>(`/api/search?q=${encodeURIComponent(query.value.trim())}`);
  } finally {
    loading.value = false;
  }
};

watch(query, () => debounce(() => { void search(); }));

const visitProject = (id: number): void => {
  results.value = null;
  router.visit(`/projects/${id}`);
};

const visitTasks = (): void => {
  results.value = null;
  router.visit(`/tasks?search=${encodeURIComponent(query.value)}`);
};
</script>

<template>
  <div class="relative w-full max-w-md">
    <label class="sr-only" for="global-search">Búsqueda global</label>
    <input
      id="global-search"
      v-model="query"
      type="search"
      autocomplete="off"
      placeholder="Buscar proyectos, tareas o usuarios"
      class="w-full rounded-lg border-slate-300 bg-white py-2 pl-9 pr-3 text-sm dark:border-slate-700 dark:bg-slate-900"
    >
    <span class="pointer-events-none absolute left-3 top-2.5 text-slate-400" aria-hidden="true">⌕</span>
    <div v-if="query.length >= 2" class="absolute z-30 mt-2 w-full overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-900">
      <p v-if="loading" class="p-3 text-sm text-slate-500">Buscando…</p>
      <p v-else-if="!hasResults" class="p-3 text-sm text-slate-500">Sin resultados.</p>
      <template v-else>
        <div v-if="unwrapCollection(results!.projects).length" class="border-b border-slate-100 p-2 dark:border-slate-800">
          <p class="px-2 py-1 text-xs font-semibold uppercase text-slate-400">Proyectos</p>
          <button v-for="project in unwrapCollection(results!.projects)" :key="project.id" class="block w-full rounded px-2 py-1.5 text-left text-sm hover:bg-slate-100 dark:hover:bg-slate-800" @click="visitProject(project.id)">{{ project.name }}</button>
        </div>
        <div v-if="unwrapCollection(results!.tasks).length" class="border-b border-slate-100 p-2 dark:border-slate-800">
          <p class="px-2 py-1 text-xs font-semibold uppercase text-slate-400">Tareas</p>
          <button v-for="task in unwrapCollection(results!.tasks)" :key="task.id" class="block w-full rounded px-2 py-1.5 text-left text-sm hover:bg-slate-100 dark:hover:bg-slate-800" @click="visitTasks">{{ task.title }}</button>
        </div>
        <div v-if="unwrapCollection(results!.users).length" class="p-2">
          <p class="px-2 py-1 text-xs font-semibold uppercase text-slate-400">Usuarios</p>
          <p v-for="user in unwrapCollection(results!.users)" :key="user.id" class="px-2 py-1.5 text-sm">{{ user.name }}</p>
        </div>
      </template>
    </div>
  </div>
</template>
