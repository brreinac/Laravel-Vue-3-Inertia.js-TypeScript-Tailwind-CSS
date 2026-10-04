import { onBeforeUnmount, onMounted } from 'vue';
import { getEcho } from '../realtime';
import type { TaskStatus } from '../types';

interface StatusEvent {
  task: {
    id: number;
    project_id: number;
    status: TaskStatus;
    completed_at: string | null;
    updated_at: string;
  };
}

export function useRealtimeTasks(projectId: number, onStatusChanged: (event: StatusEvent) => void): void {
  const channel = `projects.${projectId}`;

  onMounted(() => {
    getEcho()?.private(channel).listen('.task.status.changed', onStatusChanged);
  });

  onBeforeUnmount(() => {
    getEcho()?.leave(channel);
  });
}
