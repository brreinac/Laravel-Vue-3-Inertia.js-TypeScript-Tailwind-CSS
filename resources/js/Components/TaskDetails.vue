<script setup lang="ts">
import { ref } from 'vue';
import { api, ApiError } from '../lib/api';
import BaseButton from './ui/BaseButton.vue';
import type { Comment, Task } from '../types';

const props = defineProps<{ task: Task }>();
const emit = defineEmits<{ close: []; commentAdded: [comment: Comment] }>();
const body = ref('');
const sending = ref(false);
const error = ref('');

const submit = async (): Promise<void> => {
  if (!body.value.trim()) return;
  sending.value = true;
  error.value = '';
  try {
    const comment = await api<Comment>(`/api/tasks/${props.task.id}/comments`, { method: 'POST', body: { body: body.value } });
    body.value = '';
    emit('commentAdded', comment);
  } catch (exception) {
    error.value = exception instanceof ApiError ? exception.message : 'No se pudo enviar el comentario.';
  } finally {
    sending.value = false;
  }
};
</script>

<template>
  <section class="space-y-6">
    <div class="flex items-start justify-between gap-4">
      <div><h2 class="text-xl font-bold">{{ task.title }}</h2><p class="muted">{{ task.description || 'Sin descripción' }}</p></div>
      <BaseButton variant="ghost" aria-label="Cerrar detalle" @click="emit('close')">×</BaseButton>
    </div>
    <div class="grid gap-3 text-sm sm:grid-cols-3">
      <p><span class="font-semibold">Estado:</span> {{ task.status.replace('_', ' ') }}</p>
      <p><span class="font-semibold">Asignado:</span> {{ task.assignee?.name || 'Sin asignar' }}</p>
      <p><span class="font-semibold">Vence:</span> {{ task.due_date || 'Sin fecha' }}</p>
    </div>
    <div>
      <h3 class="mb-3 font-semibold">Comentarios</h3>
      <div class="space-y-3"><p v-for="comment in task.comments ?? []" :key="comment.id" class="rounded-lg bg-slate-100 p-3 text-sm dark:bg-slate-800"><span class="font-semibold">{{ comment.user?.name }}:</span> {{ comment.body }}</p></div>
      <form class="mt-3 space-y-2" @submit.prevent="submit"><label class="sr-only" for="comment">Nuevo comentario</label><textarea id="comment" v-model="body" rows="2" class="w-full rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900" placeholder="Escribe un comentario" /><p v-if="error" class="text-sm text-rose-600">{{ error }}</p><BaseButton type="submit" :loading="sending">Comentar</BaseButton></form>
    </div>
    <div>
      <h3 class="mb-3 font-semibold">Actividad</h3>
      <ol class="space-y-2"><li v-for="log in task.activity_logs ?? []" :key="log.id" class="text-sm text-slate-600 dark:text-slate-300"><span class="font-medium">{{ log.user?.name || 'Sistema' }}:</span> {{ log.description }}</li></ol>
    </div>
  </section>
</template>
