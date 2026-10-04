import type { ApiErrorBody } from '../types';

export class ApiError extends Error {
  public readonly status: number;
  public readonly errors?: Record<string, string[]>;

  constructor(status: number, body: ApiErrorBody) {
    super(body.message);
    this.status = status;
    this.errors = body.errors;
  }
}

interface ApiOptions extends Omit<RequestInit, 'body' | 'headers'> {
  body?: unknown;
  headers?: HeadersInit;
}

function token(): string | null {
  return typeof window === 'undefined' ? null : window.localStorage.getItem('qvox.jwt');
}

export async function api<T>(url: string, options: ApiOptions = {}): Promise<T> {
  const headers = new Headers(options.headers);
  headers.set('Accept', 'application/json');

  const jwt = token();
  if (jwt) headers.set('Authorization', `Bearer ${jwt}`);

  let body: BodyInit | undefined;
  if (options.body !== undefined) {
    headers.set('Content-Type', 'application/json');
    body = JSON.stringify(options.body);
  }

  const response = await fetch(url, { ...options, headers, body });
  if (response.status === 204) return undefined as T;

  const payload = (await response.json()) as T | ApiErrorBody;
  if (!response.ok) throw new ApiError(response.status, payload as ApiErrorBody);

  return payload as T;
}

export function toQuery(filters: Record<string, string | number | null | undefined>): string {
  const params = new URLSearchParams();
  Object.entries(filters).forEach(([key, value]) => {
    if (value !== null && value !== undefined && value !== '') params.set(key, String(value));
  });

  const query = params.toString();
  return query ? `?${query}` : '';
}

export function unwrapCollection<T>(value: { data: T[] } | T[]): T[] {
  return Array.isArray(value) ? value : value.data;
}
