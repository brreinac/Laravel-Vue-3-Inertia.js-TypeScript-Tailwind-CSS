<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Chart, DoughnutController, ArcElement, Tooltip, Legend } from 'chart.js';
import { taskStatusLabels, type TaskStatus } from '../types';

Chart.register(DoughnutController, ArcElement, Tooltip, Legend);

const props = defineProps<{ data: Record<TaskStatus, number> }>();
const canvas = ref<HTMLCanvasElement | null>(null);
let chart: Chart | null = null;

const draw = (): void => {
  if (!canvas.value) return;
  chart?.destroy();
  const statuses: TaskStatus[] = ['pendiente', 'en_progreso', 'en_revision', 'completada'];
  chart = new Chart(canvas.value, {
    type: 'doughnut',
    data: {
      labels: statuses.map((status) => taskStatusLabels[status]),
      datasets: [{ data: statuses.map((status) => props.data[status]), backgroundColor: ['#94a3b8', '#2563eb', '#f59e0b', '#16a34a'], borderWidth: 0 }],
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
  });
};

onMounted(draw);
watch(() => props.data, draw, { deep: true });
onBeforeUnmount(() => chart?.destroy());
</script>

<template>
  <div class="h-72"><canvas ref="canvas" aria-label="Distribución de tareas por estado" role="img" /></div>
</template>
