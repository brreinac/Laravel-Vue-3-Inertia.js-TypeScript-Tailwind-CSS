import { computed, ref } from 'vue';
import { defineStore } from 'pinia';
import { api } from '../lib/api';
import { connectRealtime, disconnectRealtime } from '../realtime';
import type { LoginResponse, User } from '../types';

const tokenKey = 'qvox.jwt';

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null);
  const loading = ref(false);
  const initialized = ref(false);
  const token = ref<string | null>(typeof window === 'undefined' ? null : window.localStorage.getItem(tokenKey));
  const isAuthenticated = computed(() => token.value !== null && user.value !== null);

  const persist = (nextToken: string | null): void => {
    token.value = nextToken;
    if (nextToken) window.localStorage.setItem(tokenKey, nextToken);
    else window.localStorage.removeItem(tokenKey);
  };

  const login = async (email: string, password: string): Promise<void> => {
    loading.value = true;
    try {
      const response = await api<LoginResponse>('/login', { method: 'POST', body: { email, password } });
      persist(response.access_token);
      user.value = response.user;
      connectRealtime(response.access_token);
    } finally {
      loading.value = false;
    }
  };

  const loadUser = async (): Promise<void> => {
    if (!token.value) {
      initialized.value = true;
      return;
    }

    loading.value = true;
    try {
      user.value = await api<User>('/api/auth/me');
      connectRealtime(token.value);
    } catch {
      persist(null);
      user.value = null;
    } finally {
      loading.value = false;
      initialized.value = true;
    }
  };

  const logout = async (): Promise<void> => {
    try {
      if (token.value) await api<void>('/api/logout', { method: 'POST' });
    } finally {
      disconnectRealtime();
      persist(null);
      user.value = null;
    }
  };

  return { user, loading, initialized, token, isAuthenticated, login, loadUser, logout };
});
