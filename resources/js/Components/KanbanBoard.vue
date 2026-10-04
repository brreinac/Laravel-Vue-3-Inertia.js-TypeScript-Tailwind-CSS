<script setup lang="ts">
import { computed, ref } from 'vue';
import { taskStatusLabels, type Task, type TaskStatus } from '../types';

const props = defineProps<{ tasks: Task[] }>();
const emit = defineEmits<{ move: [task: Task, status: TaskStatus]; select: [task: Task] }>();
const draggedId = ref<number | null>(null);
const columns: TaskStatus[] = ['pendiente', 'en_progreso', 'en_revision', 'completada'];
const tasksByStatus = computed(() => Object.fromEntries(columns.map((status) => [status, props.tasks.filter((task) => task.status === status)])) as Record<TaskStatus, Task[]>);

const drop = (status: TaskStatus): void => {
  const task = props.tasks.find((item) => item.id === draggedId.value);
  if (task && task.status !== status) emit('move', task, status);
  draggedId.value = null;
};
</script>

<template>
  <div class="grid gap-4 xl:grid-cols-4">
    <section v-for="status in columns" :key="status" class="rounded-xl bg-slate-100 p-3 dark:bg-slate-900" @dragover.prevent @drop="drop(status)">
      <h3 class="mb-3 text-sm font-bold text-slate-700 dark:text-slate-200">{{ taskStatusLabels[status] }} <span class="text-slate-400">{{ tasksByStatus[status].length }}</span></h3>
      <div class="min-h-28 space-y-3">
        <button v-for="task in tasksByStatus[status]" :key="task.id" draggable="true" class="block w-full rounded-lg border border-slate-200 bg-white p-3 text-left shadow-sm hover:border-brand-500 dark:border-slate-700 dark:bg-slate-800" @dragstart="draggedId = task.id" @click="emit('select', task)">
          <p class="font-semibold">{{ task.title }}</p><p class="mt-1 text-xs text-slate-500">{{ task.assignee?.name || 'Sin asignar' }} · {{ task.due_date || 'Sin fecha' }}</p>
        </button>
      </div>
    </section>
  </div>
</template>
