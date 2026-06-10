<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="text-sm font-medium">Empresa</label>
            <select wire:model="company_id" class="w-full rounded-lg border-gray-300">
                <option value="">Todas</option>
                @foreach (($this->companies ?? []) as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-sm font-medium">Data Inicial</label>
            <input type="date" wire:model="start_date" class="w-full rounded-lg border-gray-300" />
        </div>

        <div>
            <label class="text-sm font-medium">Data Final</label>
            <input type="date" wire:model="end_date" class="w-full rounded-lg border-gray-300" />
        </div>

        <div class="flex items-end gap-2">
            <x-filament::button wire:click="generateReport">
                Verificar
            </x-filament::button>

            <x-filament::button color="gray" wire:click="clearReport">
                Limpar
            </x-filament::button>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="rounded-xl border p-4">
            <div class="text-sm text-gray-500">Total</div>
            <div class="text-2xl font-bold">{{ $this->totalInconsistencies ?? 0 }}</div>
        </div>

        <div class="rounded-xl border p-4">
            <div class="text-sm text-gray-500">Sem vínculo Sólides</div>
            <div class="text-2xl font-bold">{{ $this->totalWithoutSolides ?? 0 }}</div>
        </div>

        <div class="rounded-xl border p-4">
            <div class="text-sm text-gray-500">Sem jornada</div>
            <div class="text-2xl font-bold">{{ $this->totalWithoutSchedule ?? 0 }}</div>
        </div>

        <div class="rounded-xl border p-4">
            <div class="text-sm text-gray-500">Problemas de batidas</div>
            <div class="text-2xl font-bold">{{ $this->totalPunchProblems ?? 0 }}</div>
        </div>
    </div>

    <div class="mt-6 rounded-xl border p-4">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-bold">Inconsistências encontradas</h2>
                <p class="text-sm text-gray-500">
                    Confira e ajuste antes de processar o fechamento de ponto.
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border px-2 py-2 text-left">Colaborador</th>
                        <th class="border px-2 py-2 text-left">Empresa</th>
                        <th class="border px-2 py-2 text-left">Data</th>
                        <th class="border px-2 py-2 text-left">Tipo</th>
                        <th class="border px-2 py-2 text-left">Descrição</th>
                        <th class="border px-2 py-2 text-center">Batidas</th>
                        <th class="border px-2 py-2 text-center">Ação</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse (($this->rows ?? []) as $row)
                        <tr>
                            <td class="border px-2 py-2 font-medium">
                                {{ $row['employee_name'] ?? '-' }}
                            </td>

                            <td class="border px-2 py-2">
                                {{ $row['company'] ?? '-' }}
                            </td>

                            <td class="border px-2 py-2">
                                {{ $row['date'] ?? '-' }}
                            </td>

                            <td class="border px-2 py-2">
                                @php
                                    $severity = $row['severity'] ?? 'warning';
                                    $badgeClass = match ($severity) {
                                        'danger' => 'bg-red-100 text-red-700',
                                        'success' => 'bg-green-100 text-green-700',
                                        default => 'bg-yellow-100 text-yellow-700',
                                    };
                                @endphp

                                <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-semibold {{ $badgeClass }}">
                                    {{ $row['type'] ?? '-' }}
                                </span>
                            </td>

                            <td class="border px-2 py-2">
                                {{ $row['description'] ?? '-' }}
                            </td>

                            <td class="border px-2 py-2 text-center">
                                {{ $row['entries_count'] ?? '-' }}
                            </td>

                            <td class="border px-2 py-2 text-center">
                                @if (! empty($row['adjust_url']))
                                    <a href="{{ $row['adjust_url'] }}" target="_blank" class="text-primary-600 font-semibold hover:underline">
                                        Ajustar
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="border px-2 py-8 text-center text-gray-500">
                                Nenhuma verificação executada ou nenhuma inconsistência encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
