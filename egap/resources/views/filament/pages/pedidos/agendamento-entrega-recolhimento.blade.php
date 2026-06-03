<x-filament-panels::page>
    <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-200 px-4 py-4 dark:border-gray-800 sm:px-6">
            <h2 class="text-base font-semibold text-gray-950 dark:text-white">
                Termos vinculados a entrega ou recolhimento
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Listagem baseada nas solicitações logísticas do tipo transporte de carga vinculadas aos termos com arquivo pendente ou invalidado.
            </p>
        </div>

        <div class="p-4 sm:p-6">
            {{ $this->table }}
        </div>
    </section>
</x-filament-panels::page>
