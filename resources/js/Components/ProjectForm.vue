<script setup lang="ts">
import { reactive, watch } from 'vue';
import BaseButton from './ui/BaseButton.vue';
import BaseInput from './ui/BaseInput.vue';
import BaseSelect from './ui/BaseSelect.vue';
import type { Project, ProjectPayload, ProjectStatus, User } from '../types';

const props = withDefaults(defineProps<{
  project?: Project | null;
  users: User[];
  loading?: boolean;
  errors?: Record<string, string>;
}>(), { project: null, loading: false, errors: () => ({}) });
const emit = defineEmits<{ submit: [payload: ProjectPayload]; cancel: [] }>();
const form = reactive<ProjectPayload>({ name: '', description: null, status: 'activo', owner_id: null });

watch(() => props.project, (project) => {
  Object.assign(form, project ? { name: project.name, description: project.description, status: project.status, owner_id: project.owner_id } : { name: '', description: null, status: 'activo', owner_id: null });
}, { immediate: true });
</script>

<template>
  <form class="space-y-4" @submit.prevent="emit('submit', { ...form })">
    <BaseInput id="project-name" v-model="form.name" label="Nombre" required :error="errors.name" />
    <div class="space-y-1.5"><label for="project-description" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Descripción</label><textarea id="project-description" v-model="form.description" rows="3" class="block w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900" /></div>
    <div class="grid gap-4 sm:grid-cols-2"><BaseSelect id="project-form-status" :model-value="form.status" label="Estado" :options="[{ value: 'activo', label: 'Activo' }, { value: 'archivado', label: 'Archivado' }]" @update:model-value="form.status = $event as ProjectStatus" /><BaseSelect id="project-owner" :model-value="form.owner_id" label="Responsable" :options="users.map((user) => ({ value: user.id, label: user.name }))" @update:model-value="form.owner_id = $event === '' ? null : Number($event)" /></div>
    <div class="flex justify-end gap-3"><BaseButton variant="secondary" @click="emit('cancel')">Cancelar</BaseButton><BaseButton type="submit" :loading="loading">Guardar proyecto</BaseButton></div>
  </form>
</template>
