<x-filament-panels::page>
    <meta charset="UTF-8">
    <div class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            {{ $this->form }}
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">
                Exportação Contábil da Folha
            </h2>

            <p class="mt-2 text-sm text-gray-600">
                Esta exportação gera um CSV detalhado por colaborador e evento da folha, pronto para envio à contabilidade externa.
            </p>

            <p class="mt-2 text-sm text-gray-600">
                O arquivo será composto por competência, pagamento, empresa, obra, colaborador, evento, tipo, contas contábeis, valor e histórico.
            </p>
        </div>
    </div>
</x-filament-panels::page>