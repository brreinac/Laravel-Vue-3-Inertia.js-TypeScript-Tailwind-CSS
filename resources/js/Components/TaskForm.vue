<script setup lang="ts">
import { reactive, watch } from 'vue';
import BaseButton from './ui/BaseButton.vue';
import BaseInput from './ui/BaseInput.vue';
import BaseSelect, { type SelectOption } from './ui/BaseSelect.vue';
import { taskPriorityLabels, taskStatusLabels, type Project, type Task, type TaskPayload, type TaskPriority, type TaskStatus, type User } from '../types';

const props = withDefaults(defineProps<{
  task?: Task | null;
  projects: Project[];
  users: User[];
  loading?: boolean;
  errors?: Record<string, string>;
}>(), { task: null, loading: false, errors: () => ({}) });

const emit = defineEmits<{ submit: [payload: TaskPayload]; cancel: [] }>();

const empty = (): TaskPayload => ({
  title: '', description: null, status: 'pendiente', priority: 'media', project_id: 0, assigned_to: null, due_date: null,
});
const form = reactive<TaskPayload>(empty());

const hydrate = (): void => {
  Object.assign(form, props.task ? {
    title: props.task.title,
    description: props.task.description,
    status: props.task.status,
    priority: props.task.priority,
    project_id: props.task.project_id,
    assigned_to: props.task.assigned_to,
    due_date: props.task.due_date,
  } : empty());
};

watch(() => props.task, hydrate, { immediate: true });

const statusOptions: SelectOption[] = Object.entries(taskStatusLabels).map(([value, label]) => ({ value, label }));
const priorityOptions: SelectOption[] = Object.entries(taskPriorityLabels).map(([value, label]) => ({ value, label }));

const submit = (): void => emit('submit', { ...form });
</script>

<template>
  <form class="space-y-4" @submit.prevent="submit">
    <BaseInput id="task-title" v-model="form.title" label="Título" required :error="errors.title" />
    <div class="space-y-1.5">
      <label for="task-description" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Descripción</label>
      <textarea id="task-description" v-model="form.description" rows="3" class="block w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900" />
      <p v-if="errors.description" class="text-sm text-rose-600">{{ errors.description }}</p>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
      <BaseSelect id="task-status" :model-value="form.status" label="Estado" required :options="statusOptions" :error="errors.status" @update:model-value="form.status = $event as TaskStatus" />
      <BaseSelect id="task-priority" :model-value="form.priority" label="Prioridad" required :options="priorityOptions" :error="errors.priority" @update:model-value="form.priority = $event as TaskPriority" />
      <BaseSelect id="task-project" :model-value="form.project_id" label="Proyecto" required :options="projects.map((project) => ({ value: project.id, label: project.name }))" :error="errors.project_id" @update:model-value="form.project_id = Number($event)" />
      <BaseSelect id="task-assignee" :model-value="form.assigned_to" label="Asignar a" :options="users.map((user) => ({ value: user.id, label: user.name }))" :error="errors.assigned_to" @update:model-value="form.assigned_to = $event === '' ? null : Number($event)" />
      <BaseInput id="task-due-date" v-model="form.due_date" label="Fecha límite" type="date" :error="errors.due_date" />
    </div>
    <div class="flex justify-end gap-3">
      <BaseButton variant="secondary" @click="emit('cancel')">Cancelar</BaseButton>
      <BaseButton type="submit" :loading="loading">{{ task ? 'Guardar cambios' : 'Crear tarea' }}</BaseButton>
    </div>
  </form>
</template>
