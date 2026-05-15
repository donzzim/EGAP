import * as SecureStore from 'expo-secure-store';
import { ENV } from '../config/env';
import { ApiError, NetworkError } from './errors';

// ---------------------------------------------------------------------------
// Constantes
// ---------------------------------------------------------------------------

const TOKEN_KEY = 'auth_token';

// Header obrigatório para o ngrok não retornar página HTML de aviso
const NGROK_HEADER = { 'ngrok-skip-browser-warning': '1' };

// ---------------------------------------------------------------------------
// Gerenciamento de token (SecureStore)
// ---------------------------------------------------------------------------

export const tokenStorage = {
    async get(): Promise<string | null> {
        return SecureStore.getItemAsync(TOKEN_KEY);
    },

    async set(token: string): Promise<void> {
        await SecureStore.setItemAsync(TOKEN_KEY, token);
    },

    async remove(): Promise<void> {
        await SecureStore.deleteItemAsync(TOKEN_KEY);
    },
};

// ---------------------------------------------------------------------------
// Tipos
// ---------------------------------------------------------------------------

type HttpMethod = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';

interface RequestOptions {
    method?: HttpMethod;
    body?: unknown;
    /** Se false, não inclui o token Authorization (ex: rota de login) */
    authenticated?: boolean;
}

interface ApiResponse<T = unknown> {
    status: number;
    data: T;
}

// ---------------------------------------------------------------------------
// Função central de requisição
// ---------------------------------------------------------------------------

export async function request<T = unknown>(
    endpoint: string,
    options: RequestOptions = {},
): Promise<ApiResponse<T>> {
    const { method = 'GET', body, authenticated = true } = options;

    // Monta headers base
    const headers: Record<string, string> = {
        'Content-Type': 'application/json',
        'Accept':       'application/json',
        ...NGROK_HEADER,
    };

    // Adiciona token Bearer se necessário
    if (authenticated) {
        const token = await tokenStorage.get();
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }
    }

    const url = `${ENV.API_URL}${endpoint}`;

    try {
        const response = await fetch(url, {
            method,
            headers,
            body: body !== undefined ? JSON.stringify(body) : undefined,
        });

        // Tenta parsear o JSON mesmo em caso de erro HTTP
        let data: T;
        try {
            data = await response.json();
        } catch {
            data = {} as T;
        }

        if (!response.ok) {
            throw new ApiError(response.status, (data as any)?.message ?? 'Erro na requisição.', data);
        }

        return { status: response.status, data };

    } catch (err) {
        if (err instanceof ApiError) throw err;

        // Erro de rede (sem internet, servidor fora, ngrok expirado, etc.)
        throw new NetworkError('Não foi possível conectar ao servidor. Verifique sua conexão.');
    }
}

// ---------------------------------------------------------------------------
// Atalhos semânticos
// ---------------------------------------------------------------------------

export const apiClient = {
    get<T>(endpoint: string, authenticated = true) {
        return request<T>(endpoint, { method: 'GET', authenticated });
    },

    post<T>(endpoint: string, body: unknown, authenticated = true) {
        return request<T>(endpoint, { method: 'POST', body, authenticated });
    },

    put<T>(endpoint: string, body: unknown, authenticated = true) {
        return request<T>(endpoint, { method: 'PUT', body, authenticated });
    },

    patch<T>(endpoint: string, body: unknown, authenticated = true) {
        return request<T>(endpoint, { method: 'PATCH', body, authenticated });
    },

    delete<T>(endpoint: string, authenticated = true) {
        return request<T>(endpoint, { method: 'DELETE', authenticated });
    },
};