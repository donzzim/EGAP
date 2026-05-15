import * as SecureStore from 'expo-secure-store';
import { apiClient, tokenStorage } from './client';

const USER_KEY = 'auth_user';

export interface MobileUser {
    id: number;
    idEgap: number | null;
    login: string | null;
    name: string | null;
    email: string | null;
    unidade_judiciaria: number | string | null;
    setor: number | string | null;
    token: string;
}

export interface AuthSession {
    token: string;
    user: MobileUser;
}

interface LoginResponse {
    message: string;
    user: MobileUser;
}

interface MeResponse {
    user: unknown;
}

async function setStoredUser(user: MobileUser): Promise<void> {
    await SecureStore.setItemAsync(USER_KEY, JSON.stringify(user));
}

async function getStoredUser(): Promise<MobileUser | null> {
    const rawUser = await SecureStore.getItemAsync(USER_KEY);

    if (!rawUser) {
        return null;
    }

    try {
        return JSON.parse(rawUser) as MobileUser;
    } catch {
        await SecureStore.deleteItemAsync(USER_KEY);
        return null;
    }
}

async function clearStoredSession(): Promise<void> {
    await Promise.all([
        tokenStorage.remove(),
        SecureStore.deleteItemAsync(USER_KEY),
    ]);
}

export const authApi = {
    async login(login: string, password: string): Promise<AuthSession> {
        const { data } = await apiClient.post<LoginResponse>(
            '/login',
            { login, password },
            false,
        );

        await Promise.all([
            tokenStorage.set(data.user.token),
            setStoredUser(data.user),
        ]);

        return {
            token: data.user.token,
            user: data.user,
        };
    },

    async getStoredSession(): Promise<AuthSession | null> {
        const [token, user] = await Promise.all([
            tokenStorage.get(),
            getStoredUser(),
        ]);

        if (!token || !user) {
            return null;
        }

        return { token, user };
    },

    async validateSession(): Promise<boolean> {
        try {
            await apiClient.get<MeResponse>('/me');
            return true;
        } catch {
            await clearStoredSession();
            return false;
        }
    },

    async logout(): Promise<void> {
        try {
            await apiClient.post('/logout', {});
        } finally {
            await clearStoredSession();
        }
    },

    clearStoredSession,
};
