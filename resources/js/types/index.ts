export type UserRole = 'admin' | 'member';
export type ProjectStatus = 'activo' | 'archivado';
export type TaskStatus = 'pendiente' | 'en_progreso' | 'en_revision' | 'completada';
export type TaskPriority = 'baja' | 'media' | 'alta' | 'crítica';

export interface User {
  id: number;
  name: string;
  email: string;
  role: UserRole;
  avatar: string | null;
}

export interface Project {
  id: number;
  name: string;
  description: string | null;
  status: ProjectStatus;
  owner_id: number;
  owner?: User;
  tasks?: Task[];
  created_at: string;
  updated_at: string;
}

export interface Task {
  id: number;
  title: string;
  description: string | null;
  status: TaskStatus;
  priority: TaskPriority;
  project_id: number;
  project?: Project;
  assigned_to: number | null;
  assignee?: User | null;
  due_date: string | null;
  completed_at: string | null;
  comments?: Comment[];
  activity_logs?: ActivityLog[];
  created_at: string;
  updated_at: string;
}

export interface Comment {
  id: number;
  body: string;
  task_id: number;
  user?: User;
  created_at: string;
}

export interface ActivityLog {
  id: number;
  old_status: TaskStatus | null;
  new_status: TaskStatus;
  description: string;
  user?: User;
  created_at: string;
}

export interface PaginationMeta {
  current_page: number;
  from: number | null;
  last_page: number;
  per_page: number;
  to: number | null;
  total: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  links: Array<{ url: string | null; label: string; active: boolean }>;
  meta: PaginationMeta;
}

export interface ApiErrorBody {
  message: string;
  errors?: Record<string, string[]>;
}

export interface LoginResponse {
  access_token: string;
  token_type: 'bearer';
  expires_in: number;
  user: User;
}

export interface DashboardResponse {
  statistics: Record<TaskStatus, number>;
  upcoming_tasks: { data: Task[] } | Task[];
}

export interface SearchResults {
  projects: { data: Project[] } | Project[];
  tasks: { data: Task[] } | Task[];
  users: { data: User[] } | User[];
}

export interface TaskPayload {
  title: string;
  description: string | null;
  status: TaskStatus;
  priority: TaskPriority;
  project_id: number;
  assigned_to: number | null;
  due_date: string | null;
}

export interface ProjectPayload {
  name: string;
  description: string | null;
  status: ProjectStatus;
  owner_id: number | null;
}

export const taskStatusLabels: Record<TaskStatus, string> = {
  pendiente: 'Pendiente',
  en_progreso: 'En progreso',
  en_revision: 'En revisión',
  completada: 'Completada',
};

export const taskPriorityLabels: Record<TaskPriority, string> = {
  baja: 'Baja',
  media: 'Media',
  alta: 'Alta',
  crítica: 'Crítica',
};
