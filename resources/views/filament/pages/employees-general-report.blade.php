<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit.prevent="generate" class="space-y-6">
            {{ $this->form }}

            <div class="flex flex-wrap gap-3">
                <x-filament::button type="submit">
                    Gerar Relatório
                </x-filament::button>

                <x-filament::button color="success" type="button" wire:click="exportPdf">
                    Exportar PDF
                </x-filament::button>
            </div>
        </form>

        @if($employees->count())
            <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-100 dark:bg-gray-800">
                            <th class="p-2 text-left">Nome</th>
                            <th class="p-2 text-left">CPF</th>
                            <th class="p-2 text-left">Obra</th>
                            <th class="p-2 text-left">Cargo</th>
                            <th class="p-2 text-left">Departamento</th>
                            <th class="p-2 text-left">Admissão</th>
                            <th class="p-2 text-left">Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($employees as $employee)
                            <tr class="border-t border-gray-200 dark:border-gray-700">
                                <td class="p-2">{{ $employee->name }}</td>
                                <td class="p-2">{{ $employee->cpf ?? '-' }}</td>
                                <td class="p-2">{{ $employee->work?->name ?? 'Sem obra' }}</td>
                                <td class="p-2">{{ $employee->jobRole?->name ?? '-' }}</td>
                                <td class="p-2">{{ $employee->department?->name ?? '-' }}</td>
                                <td class="p-2">
                                    {{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="p-2">{{ $employee->status ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-6 text-sm text-gray-500 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400">
                Nenhum relatório gerado ainda. Use os filtros acima e clique em <strong>Gerar Relatório</strong>.
            </div>
        @endif
    </div>
</x-filament-panels::page>