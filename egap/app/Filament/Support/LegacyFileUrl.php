<?php

namespace App\Filament\Support;

use Illuminate\Support\Facades\Storage;

class LegacyFileUrl
{
    /**
     * Resolve um caminho de arquivo (armazenado no formato legado do
     * sistema antigo, ex.: `/images/termos/x.pdf`, ou já no formato deste
     * projeto, ex.: `files/pedidos/x.pdf`) para uma URL.
     *
     * Se o arquivo já existir no disco `public` deste projeto — ou seja, se
     * foi enviado ou atualizado por aqui —, a URL aponta para cá. Caso
     * contrário, mantém o comportamento legado de apontar para o sistema
     * antigo, de onde os registros ainda não migrados continuam sendo
     * servidos.
     */
    public static function resolve(?string $path, string $legacyBaseUrl): ?string
    {
        if (blank($path)) {
            return null;
        }

        $relativePath = ltrim($path, '/');

        if (Storage::disk('public')->exists($relativePath)) {
            return Storage::disk('public')->url($relativePath);
        }

        return rtrim($legacyBaseUrl, '/').'/'.$relativePath;
    }
}
