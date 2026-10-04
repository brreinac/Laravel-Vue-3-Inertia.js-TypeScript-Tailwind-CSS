<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue';
import AppShell from '../../Components/AppShell.vue';
import BaseButton from '../../Components/ui/BaseButton.vue';
import BaseInput from '../../Components/ui/BaseInput.vue';
import BasePagination from '../../Components/ui/BasePagination.vue';
import BaseSelect from '../../Components/ui/BaseSelect.vue';
import BaseTable, { type TableColumn } from '../../Components/ui/BaseTable.vue';
import TaskDetails from '../../Components/TaskDetails.vue';
import TaskForm from '../../Components/TaskForm.vue';
import { api, ApiError, toQuery } from '../../lib/api';
import { useDebounce } from '../../composables/useDebounce';
import { useAuthStore } from '../../stores/auth';
import { taskPriorityLabels, taskStatusLabels, type Comment, type PaginatedResponse, type Project, type Task, type TaskPayload, type TaskPriority, type TaskStatus, type User } from '../../types';

const auth = useAuthStore();
const response = ref<PaginatedResponse<Task> | null>(null);
const projects = ref<Project[]>([]);
const users = ref<User[]>([]);
const selected = ref<Task | null>(null);
const editing = ref<Task | null>(null);
const formOpen = ref(false);
const loading = ref(true);
const saving = ref(false);
const error = ref('');
const formErrors = reactive<Record<string, string>>({});
const filters = reactive<{ search: string; status: TaskStatus | ''; priority: TaskPriority | ''; project_id: number | '' }>({ search: '', status: '', priority: '', project_id: '' });
const debounce = useDebounce();
const columns: TableColumn[] = [{ key: 'title', label: 'Tarea' }, { key: 'project', label: 'Proyecto' }, { key: 'status', label: 'Estado' }, { key: 'priority', label: 'Prioridad' }, { key: 'due', label: 'Vence' }];
const rows = computed(() => (response.value?.data ?? []).map((task) => ({ id: task.id, title: task.title, project: task.project?.name ?? '—', status: task.status, priority: task.priority, due: task.due_date ?? '—' })));

const load = async (page = 1): Promise<void> => {
  loading.value = true;
  error.value = '';
  try {
    response.value = await api<PaginatedResponse<Task>>(`/api/tasks${toQuery({ ...filters, page })}`);
  } catch { error.value = 'No se pudieron cargar las tareas.'; } finally { loading.value = false; }
};

const loadOptions = async (): Promise<void> => {
  const projectData = await api<PaginatedResponse<Project>>('/api/projects?per_page=100');
  projects.value = projectData.data;
  if (auth.user?.role === 'admin') {
    const userData = await api<PaginatedResponse<User>>('/api/users');
    users.value = userData.data;
  }
};

watch(filters, () => debounce(() => { void load(); }), { deep: true });
onMounted(async () => {
  if (!auth.initialized) await auth.loadUser();
  await Promise.all([load(), loadOptions()]).catch(() => { error.value = 'No se pudieron cargar los datos de apoyo.'; });
});

const openCreate = (): void => { editing.value = null; formOpen.value = true; Object.keys(formErrors).forEach((key) => { delete formErrors[key]; }); };
const openEdit = async (id: number): Promise<void> => { try { editing.value = await api<Task>(`/api/tasks/${id}`); formOpen.value = true; } catch { error.value = 'No se pudo cargar la tarea.'; } };
const openDetails = async (id: number): Promise<void> => { try { selected.value = await api<Task>(`/api/tasks/${id}`); } catch { error.value = 'No se pudo cargar el detalle de la tarea.'; } };

const save = async (payload: TaskPayload): Promise<void> => {
  saving.value = true;
  Object.keys(formErrors).forEach((key) => { delete formErrors[key]; });
  try {
    if (editing.value) {
      const { status, ...details } = payload;
      let updated = await api<Task>(`/api/tasks/${editing.value.id}`, { method: 'PATCH', body: details });
      if (status !== editing.value.status) updated = await api<Task>(`/api/tasks/${editing.value.id}/status`, { method: 'PATCH', body: { status } });
      replaceTask(updated);
    } else {
      const created = await api<Task>('/api/tasks', { method: 'POST', body: payload });
      if (response.value) response.value.data.unshift(created);
    }
    formOpen.value = false;
  } catch (exception) {
    if (exception instanceof ApiError) {
      Object.entries(exception.errors ?? {}).forEach(([field, messages]) => { formErrors[field] = messages[0] ?? ''; });
      error.value = exception.message;
    } else error.value = 'No se pudo guardar la tarea.';
  } finally { saving.value = false; }
};

const replaceTask = (updated: Task): void => {
  if (response.value) response.value.data = response.value.data.map((task) => task.id === updated.id ? { ...task, ...updated } : task);
  if (selected.value?.id === updated.id) selected.value = { ...selected.value, ...updated };
};
const addComment = (comment: Comment): void => { if (selected.value) selected.value.comments = [...(selected.value.comments ?? []), comment]; };

const exportCsv = async (): Promise<void> => {
  const result = await fetch(`/api/tasks/export${toQuery(filters)}`, { headers: auth.token ? { Authorization: `Bearer ${auth.token}` } : {} });
  if (!result.ok) { error.value = 'No se pudo exportar el archivo.'; return; }
  const url = URL.createObjectURL(await result.blob());
  const anchor = document.createElement('a'); anchor.href = url; anchor.download = 'tareas.csv'; anchor.click(); URL.revokeObjectURL(url);
};
</script>

<template>
  <AppShell>
    <div class="flex flex-wrap items-end justify-between gap-4"><div><h1 class="page-title">Tareas</h1><p class="mt-1 muted">Filtra, asigna y da seguimiento al trabajo.</p></div><div class="flex gap-2"><BaseButton variant="secondary" @click="exportCsv">Exportar CSV</BaseButton><BaseButton v-if="auth.user?.role === 'admin'" @click="openCreate">Nueva tarea</BaseButton></div></div>
    <div class="surface mt-6 grid gap-4 p-4 sm:grid-cols-2 xl:grid-cols-4"><BaseInput id="task-search" v-model="filters.search" type="search" label="Buscar" placeholder="Título o descripción" /><BaseSelect id="task-filter-status" :model-value="filters.status" label="Estado" :options="Object.entries(taskStatusLabels).map(([value, label]) => ({ value, label }))" @update:model-value="filters.status = $event as TaskStatus | ''" /><BaseSelect id="task-filter-priority" :model-value="filters.priority" label="Prioridad" :options="Object.entries(taskPriorityLabels).map(([value, label]) => ({ value, label }))" @update:model-value="filters.priority = $event as TaskPriority | ''" /><BaseSelect id="task-filter-project" :model-value="filters.project_id" label="Proyecto" :options="projects.map((project) => ({ value: project.id, label: project.name }))" @update:model-value="filters.project_id = $event === '' ? '' : Number($event)" /></div>
    <p v-if="error" class="mt-4 text-rose-600" role="alert">{{ error }}</p>
    <div class="mt-6 space-y-4"><p v-if="loading" class="muted">Cargando tareas…</p><BaseTable :columns="columns" :rows="rows" empty-message="No hay tareas que coincidan con los filtros."><template #cell-title="{ row }"><button class="font-semibold text-brand-600 hover:underline" @click="openDetails(Number(row.id))">{{ row.title }}</button></template><template #cell-status="{ row }"><span class="rounded-full bg-blue-50 px-2 py-1 text-xs text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">{{ row.status }}</span></template><template #cell-priority="{ row }"><button class="hover:underline" @click="openEdit(Number(row.id))">{{ row.priority }}</button></template></BaseTable><BasePagination v-if="response" :meta="response.meta" @change="load" /></div>
    <div v-if="formOpen" class="fixed inset-0 z-40 overflow-y-auto bg-slate-950/50 p-4"><div class="mx-auto mt-8 max-w-2xl rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900"><h2 class="mb-5 text-xl font-bold">{{ editing ? 'Editar tarea' : 'Nueva tarea' }}</h2><TaskForm :task="editing" :projects="projects" :users="users" :loading="saving" :errors="formErrors" @submit="save" @cancel="formOpen = false" /></div></div>
    <div v-if="selected" class="fixed inset-0 z-40 overflow-y-auto bg-slate-950/50 p-4"><div class="mx-auto mt-12 max-w-2xl rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900"><TaskDetails :task="selected" @close="selected = null" @comment-added="addComment" /></div></div>
  </AppShell>
</template>
