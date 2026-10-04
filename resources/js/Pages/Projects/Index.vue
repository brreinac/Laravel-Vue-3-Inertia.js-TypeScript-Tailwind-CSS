<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import AppShell from '../../Components/AppShell.vue';
import ProjectForm from '../../Components/ProjectForm.vue';
import BaseButton from '../../Components/ui/BaseButton.vue';
import BaseInput from '../../Components/ui/BaseInput.vue';
import BasePagination from '../../Components/ui/BasePagination.vue';
import BaseSelect from '../../Components/ui/BaseSelect.vue';
import BaseTable, { type TableColumn } from '../../Components/ui/BaseTable.vue';
import { api, ApiError, toQuery } from '../../lib/api';
import { useDebounce } from '../../composables/useDebounce';
import { useAuthStore } from '../../stores/auth';
import type { PaginatedResponse, Project, ProjectPayload, ProjectStatus, User } from '../../types';

const response = ref<PaginatedResponse<Project> | null>(null);
const filters = reactive<{ search: string; status: ProjectStatus | '' }>({ search: '', status: '' });
const loading = ref(true);
const error = ref('');
const users = ref<User[]>([]);
const editing = ref<Project | null>(null);
const formOpen = ref(false);
const saving = ref(false);
const formErrors = reactive<Record<string, string>>({});
const auth = useAuthStore();
const debounce = useDebounce();
const columns: TableColumn[] = [{ key: 'name', label: 'Proyecto' }, { key: 'status', label: 'Estado' }, { key: 'owner', label: 'Responsable' }];
const rows = computed(() => (response.value?.data ?? []).map((project) => ({ id: project.id, name: project.name, status: project.status, owner: project.owner?.name ?? '—' })));

const load = async (page = 1): Promise<void> => {
  loading.value = true;
  error.value = '';
  try {
    response.value = await api<PaginatedResponse<Project>>(`/api/projects${toQuery({ ...filters, page })}`);
  } catch {
    error.value = 'No se pudieron cargar los proyectos.';
  } finally { loading.value = false; }
};

watch(filters, () => debounce(() => { void load(); }), { deep: true });
onMounted(async () => {
  if (!auth.initialized) await auth.loadUser();
  await load();
  if (auth.user?.role === 'admin') {
    const userData = await api<PaginatedResponse<User>>('/api/users');
    users.value = userData.data;
  }
});

const openCreate = (): void => { editing.value = null; formOpen.value = true; };
const openEdit = async (id: number): Promise<void> => { editing.value = await api<Project>(`/api/projects/${id}`); formOpen.value = true; };
const save = async (payload: ProjectPayload): Promise<void> => {
  saving.value = true;
  Object.keys(formErrors).forEach((key) => { delete formErrors[key]; });
  try {
    if (editing.value) {
      const updated = await api<Project>(`/api/projects/${editing.value.id}`, { method: 'PATCH', body: payload });
      if (response.value) response.value.data = response.value.data.map((project) => project.id === updated.id ? updated : project);
    } else {
      const created = await api<Project>('/api/projects', { method: 'POST', body: payload });
      if (response.value) response.value.data.unshift(created);
    }
    formOpen.value = false;
  } catch (exception) {
    if (exception instanceof ApiError) Object.entries(exception.errors ?? {}).forEach(([field, messages]) => { formErrors[field] = messages[0] ?? ''; });
    error.value = exception instanceof ApiError ? exception.message : 'No se pudo guardar el proyecto.';
  } finally { saving.value = false; }
};
</script>

<template>
  <AppShell>
    <div class="flex items-end justify-between gap-4"><div><h1 class="page-title">Proyectos</h1><p class="mt-1 muted">Consulta el estado y las tareas de cada iniciativa.</p></div><BaseButton v-if="auth.user?.role === 'admin'" @click="openCreate">Nuevo proyecto</BaseButton></div>
    <div class="surface mt-6 grid gap-4 p-4 sm:grid-cols-2"><BaseInput id="project-search" v-model="filters.search" type="search" label="Buscar" placeholder="Nombre del proyecto" /><BaseSelect id="project-status" :model-value="filters.status" label="Estado" :options="[{ value: 'activo', label: 'Activo' }, { value: 'archivado', label: 'Archivado' }]" @update:model-value="filters.status = $event as ProjectStatus | ''" /></div>
    <p v-if="error" class="mt-4 text-rose-600" role="alert">{{ error }}</p>
    <div v-else class="mt-6 space-y-4"><p v-if="loading" class="muted">Cargando proyectos…</p><BaseTable :columns="columns" :rows="rows" empty-message="No hay proyectos que coincidan con los filtros."><template #cell-name="{ row }"><button class="font-semibold text-brand-600 hover:underline" @click="router.visit(`/projects/${row.id}`)">{{ row.name }}</button></template><template #cell-status="{ row }"><button class="rounded-full bg-slate-100 px-2 py-1 text-xs font-medium dark:bg-slate-800" @click="auth.user?.role === 'admin' && openEdit(Number(row.id))">{{ row.status }}</button></template></BaseTable><BasePagination v-if="response" :meta="response.meta" @change="load" /></div>
    <div v-if="formOpen" class="fixed inset-0 z-40 overflow-y-auto bg-slate-950/50 p-4"><div class="mx-auto mt-12 max-w-xl rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900"><h2 class="mb-5 text-xl font-bold">{{ editing ? 'Editar proyecto' : 'Nuevo proyecto' }}</h2><ProjectForm :project="editing" :users="users" :loading="saving" :errors="formErrors" @submit="save" @cancel="formOpen = false" /></div></div>
  </AppShell>
</template>
