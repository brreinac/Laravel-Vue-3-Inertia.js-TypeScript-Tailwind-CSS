<script setup lang="ts">
import { onMounted, ref } from 'vue';
import AppShell from '../../Components/AppShell.vue';
import BasePagination from '../../Components/ui/BasePagination.vue';
import KanbanBoard from '../../Components/KanbanBoard.vue';
import TaskDetails from '../../Components/TaskDetails.vue';
import { api, ApiError } from '../../lib/api';
import { useRealtimeTasks } from '../../composables/useRealtimeTasks';
import type { Comment, PaginatedResponse, Project, Task, TaskStatus } from '../../types';

const projectId = Number(window.location.pathname.split('/').filter(Boolean).at(-1));
const project = ref<Project | null>(null);
const tasks = ref<PaginatedResponse<Task> | null>(null);
const selected = ref<Task | null>(null);
const loading = ref(true);
const error = ref('');

const load = async (page = 1): Promise<void> => {
  loading.value = true;
  error.value = '';
  try {
    const [projectData, tasksData] = await Promise.all([
      api<Project>(`/api/projects/${projectId}`),
      api<PaginatedResponse<Task>>(`/api/projects/${projectId}/tasks?per_page=20&page=${page}`),
    ]);
    project.value = projectData;
    tasks.value = tasksData;
  } catch {
    error.value = 'No se pudo cargar el proyecto.';
  } finally { loading.value = false; }
};

const move = async (task: Task, status: TaskStatus): Promise<void> => {
  error.value = '';
  try {
    const updated = await api<Task>(`/api/tasks/${task.id}/status`, { method: 'PATCH', body: { status } });
    replaceTask(updated);
  } catch (exception) {
    error.value = exception instanceof ApiError ? exception.message : 'No se pudo cambiar el estado.';
  }
};

const replaceTask = (updated: Task): void => {
  if (!tasks.value) return;
  tasks.value.data = tasks.value.data.map((task) => task.id === updated.id ? { ...task, ...updated } : task);
  if (selected.value?.id === updated.id) selected.value = { ...selected.value, ...updated };
};

const openTask = async (task: Task): Promise<void> => {
  try { selected.value = await api<Task>(`/api/tasks/${task.id}`); } catch { error.value = 'No se pudo cargar el detalle de la tarea.'; }
};

const addComment = (comment: Comment): void => {
  if (selected.value) selected.value.comments = [...(selected.value.comments ?? []), comment];
};

useRealtimeTasks(projectId, (event) => {
  if (!tasks.value) return;
  const task = tasks.value.data.find((item) => item.id === event.task.id);
  if (task) replaceTask({ ...task, ...event.task });
});

onMounted(() => { void load(); });
</script>

<template>
  <AppShell>
    <template v-if="loading"><p class="muted">Cargando proyecto…</p></template>
    <template v-else-if="project">
      <h1 class="page-title">{{ project.name }}</h1><p class="mt-1 muted">{{ project.description || 'Sin descripción' }}</p>
      <p v-if="error" class="mt-4 rounded-lg bg-rose-50 p-3 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300" role="alert">{{ error }}</p>
      <KanbanBoard class="mt-6" :tasks="tasks?.data ?? []" @move="move" @select="openTask" />
      <BasePagination v-if="tasks" class="mt-6" :meta="tasks.meta" @change="load" />
      <div v-if="selected" class="fixed inset-0 z-40 overflow-y-auto bg-slate-950/50 p-4"><div class="mx-auto mt-12 max-w-2xl rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900"><TaskDetails :task="selected" @close="selected = null" @comment-added="addComment" /></div></div>
    </template>
    <p v-else class="text-rose-600" role="alert">{{ error }}</p>
  </AppShell>
</template>
