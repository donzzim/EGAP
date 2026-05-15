import * as SecureStore from 'expo-secure-store';
import type { BemPatrimonial } from '@/src/api/bens';

const MAX_RECENT_BENS = 5;
const STORAGE_KEY_PREFIX = 'recent_bens';

export interface RecentBem {
    id: string;
    codigo: string;
    descricao: string;
    situacao: string;
    consultedAt: string;
}

function displayValue(value: unknown, fallback = '-'): string {
    if (value === null || value === undefined || value === '') {
        return fallback;
    }

    return String(value);
}

function getStorageKey(userId: number | string): string {
    return `${STORAGE_KEY_PREFIX}:${userId}`;
}

function getBemCodigo(bem: BemPatrimonial): string {
    return displayValue(
        bem.codigo
            ?? bem.patrimonio
            ?? bem.codigo_patrimonial
            ?? bem.tombamento
            ?? bem.tombo_smarapd
            ?? bem.num_tombo_smarapd
            ?? bem.id,
        'Sem código',
    );
}

function getBemDescricao(bem: BemPatrimonial): string {
    return displayValue(bem.descricao_resumida ?? bem.descricao ?? bem.denominacao, 'Bem patrimonial');
}

function getBemSituacao(bem: BemPatrimonial): string {
    return displayValue(bem.situacao ?? bem.estado, 'Consultado');
}

function toRecentBem(bem: BemPatrimonial): RecentBem {
    const codigo = getBemCodigo(bem);

    return {
        id: displayValue(bem.id, codigo),
        codigo,
        descricao: getBemDescricao(bem),
        situacao: getBemSituacao(bem),
        consultedAt: new Date().toISOString(),
    };
}

function isRecentBem(value: unknown): value is RecentBem {
    if (typeof value !== 'object' || value === null) {
        return false;
    }

    const recentBem = value as Record<string, unknown>;

    return typeof recentBem.id === 'string'
        && typeof recentBem.codigo === 'string'
        && typeof recentBem.descricao === 'string'
        && typeof recentBem.situacao === 'string'
        && typeof recentBem.consultedAt === 'string';
}

export const recentBensStorage = {
    async list(userId: number | string): Promise<RecentBem[]> {
        const rawRecentBens = await SecureStore.getItemAsync(getStorageKey(userId));

        if (!rawRecentBens) {
            return [];
        }

        try {
            const parsedRecentBens = JSON.parse(rawRecentBens);

            if (!Array.isArray(parsedRecentBens)) {
                await SecureStore.deleteItemAsync(getStorageKey(userId));
                return [];
            }

            return parsedRecentBens.filter(isRecentBem).slice(0, MAX_RECENT_BENS);
        } catch {
            await SecureStore.deleteItemAsync(getStorageKey(userId));
            return [];
        }
    },

    async add(userId: number | string, bem: BemPatrimonial): Promise<RecentBem[]> {
        const recentBem = toRecentBem(bem);
        const currentRecentBens = await this.list(userId);
        const nextRecentBens = [
            recentBem,
            ...currentRecentBens.filter((item) => item.id !== recentBem.id && item.codigo !== recentBem.codigo),
        ].slice(0, MAX_RECENT_BENS);

        await SecureStore.setItemAsync(getStorageKey(userId), JSON.stringify(nextRecentBens));

        return nextRecentBens;
    },
};
