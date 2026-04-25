<x-filament-panels::page>
    <form wire:submit.prevent="generate" class="space-y-6">
        {{ $this->form }}

        <div class="flex gap-3">
            <x-filament::button type="submit">
                Gerar Relatório
            </x-filament::button>

            <x-filament::button color="success" wire:click="exportPdf">
                Exportar PDF
            </x-filament::button>
        </div>
    </form>

    @if($employees->count())
        <div class="mt-6 overflow-x-auto rounded-xl border bg-white shadow-sm">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100">
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
                        <tr class="border-t">
                            <td class="p-2">{{ $employee->name }}</td>
                            <td class="p-2">{{ $employee->cpf }}</td>
                            <td class="p-2">{{ $employee->work?->name ?? 'Sem obra' }}</td>
                            <td class="p-2">{{ $employee->jobRole?->name ?? '-' }}</td>
                            <td class="p-2">{{ $employee->department?->name ?? '-' }}</td>
                            <td class="p-2">{{ optional($employee->hire_date)->format('d/m/Y') }}</td>
                            <td class="p-2">{{ $employee->status }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-filament-panels::page>