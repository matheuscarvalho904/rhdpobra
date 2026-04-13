<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label class="text-sm font-medium">Competência</label>
            <select wire:model="payroll_competency_id" class="w-full rounded-lg border-gray-300">
                <option value="">Todas</option>
                @foreach (($this->competencies ?? []) as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

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
            <label class="text-sm font-medium">Filial</label>
            <select wire:model="branch_id" class="w-full rounded-lg border-gray-300">
                <option value="">Todas</option>
                @foreach (($this->branches ?? []) as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-sm font-medium">Obra</label>
            <select wire:model="work_id" class="w-full rounded-lg border-gray-300">
                <option value="">Todas</option>
                @foreach (($this->works ?? []) as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end gap-2">
            <x-filament::button wire:click="generateReport">
                Gerar
            </x-filament::button>

            <x-filament::button color="success" wire:click="exportPdf">
                PDF
            </x-filament::button>
        </div>
    </div>

    <div class="mt-6 space-y-6">
        @forelse (($this->rows ?? []) as $company => $works)
            <div class="rounded-xl border p-4">
                <h2 class="text-lg font-bold mb-3">Empresa: {{ $company }}</h2>

                @foreach (($works ?? []) as $work => $data)
                    <div class="mb-6">
                        <h3 class="font-semibold mb-2">Obra: {{ $work }}</h3>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border-collapse border border-gray-300">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="border px-2 py-2 text-left">Colaborador</th>
                                        <th class="border px-2 py-2 text-left">CPF</th>
                                        <th class="border px-2 py-2 text-left">Matrícula</th>
                                        <th class="border px-2 py-2 text-left">Cargo</th>
                                        <th class="border px-2 py-2 text-left">Filial</th>
                                        <th class="border px-2 py-2 text-left">Chave PIX</th>
                                        <th class="border px-2 py-2 text-right">Valor Líquido</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (($data['rows'] ?? []) as $row)
                                        <tr>
                                            <td class="border px-2 py-2">{{ $row['employee_name'] ?? '-' }}</td>
                                            <td class="border px-2 py-2">{{ $row['cpf'] ?? '-' }}</td>
                                            <td class="border px-2 py-2">{{ $row['registration_number'] ?? '-' }}</td>
                                            <td class="border px-2 py-2">{{ $row['job_role'] ?? '-' }}</td>
                                            <td class="border px-2 py-2">{{ $row['branch'] ?? '-' }}</td>
                                            <td class="border px-2 py-2">{{ $row['pix_key'] ?? '-' }}</td>
                                            <td class="border px-2 py-2 text-right">
                                                R$ {{ number_format((float) ($row['net_total'] ?? 0), 2, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="font-bold bg-gray-50">
                                        <td colspan="6" class="border px-2 py-2 text-right">Total da Obra</td>
                                        <td class="border px-2 py-2 text-right">
                                            R$ {{ number_format((float) ($data['total'] ?? 0), 2, ',', '.') }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @empty
            <div class="rounded-xl border p-6 text-center text-gray-500">
                Nenhum dado gerado ainda.
            </div>
        @endforelse

        <div class="rounded-xl border p-4">
            <div class="text-right text-lg font-bold">
                Total Geral: R$ {{ number_format((float) ($this->totalNet ?? 0), 2, ',', '.') }}
            </div>
        </div>
    </div>
</x-filament-panels::page>