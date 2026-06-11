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
        @if (! empty($this->rows))
            <div class="rounded-xl border p-4">
                <div class="mb-4">
                    <h2 class="text-lg font-bold">Relatório Global em Ordem Alfabética</h2>
                    <p class="text-sm text-gray-500">
                        Colaboradores listados em ordem alfabética, independente da empresa ou obra.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse border border-gray-300">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border px-2 py-2 text-left">Colaborador</th>
                                <th class="border px-2 py-2 text-left">Empresa</th>
                                <th class="border px-2 py-2 text-left">Obra</th>
                                <th class="border px-2 py-2 text-left">CPF</th>
                                <th class="border px-2 py-2 text-left">Matrícula</th>
                                <th class="border px-2 py-2 text-left">Cargo</th>
                                <th class="border px-2 py-2 text-left">Filial</th>
                                <th class="border px-2 py-2 text-left">Chave PIX</th>
                                <th class="border px-2 py-2 text-right">Salário Base</th>
                                <th class="border px-2 py-2 text-right">Bruto</th>
                                <th class="border px-2 py-2 text-right">Descontos</th>
                                <th class="border px-2 py-2 text-right">Líquido</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (($this->rows ?? []) as $row)
                                <tr>
                                    <td class="border px-2 py-2">{{ $row['employee_name'] ?? '-' }}</td>
                                    <td class="border px-2 py-2">{{ $row['company'] ?? '-' }}</td>
                                    <td class="border px-2 py-2">{{ $row['work'] ?? '-' }}</td>
                                    <td class="border px-2 py-2">{{ $row['cpf'] ?? '-' }}</td>
                                    <td class="border px-2 py-2">{{ $row['registration_number'] ?? '-' }}</td>
                                    <td class="border px-2 py-2">{{ $row['job_role'] ?? '-' }}</td>
                                    <td class="border px-2 py-2">{{ $row['branch'] ?? '-' }}</td>
                                    <td class="border px-2 py-2">{{ $row['pix_key'] ?? '-' }}</td>
                                    <td class="border px-2 py-2 text-right">
                                        R$ {{ number_format((float) ($row['base_salary'] ?? 0), 2, ',', '.') }}
                                    </td>
                                    <td class="border px-2 py-2 text-right">
                                        R$ {{ number_format((float) ($row['gross_total'] ?? 0), 2, ',', '.') }}
                                    </td>
                                    <td class="border px-2 py-2 text-right">
                                        R$ {{ number_format((float) ($row['discounts_total'] ?? 0), 2, ',', '.') }}
                                    </td>
                                    <td class="border px-2 py-2 text-right font-semibold">
                                        R$ {{ number_format((float) ($row['net_total'] ?? 0), 2, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-bold bg-gray-50">
                                <td colspan="9" class="border px-2 py-2 text-right">Totais Gerais</td>
                                <td class="border px-2 py-2 text-right">
                                    R$ {{ number_format((float) ($this->totalGross ?? 0), 2, ',', '.') }}
                                </td>
                                <td class="border px-2 py-2 text-right">
                                    R$ {{ number_format((float) ($this->totalDiscounts ?? 0), 2, ',', '.') }}
                                </td>
                                <td class="border px-2 py-2 text-right">
                                    R$ {{ number_format((float) ($this->totalNet ?? 0), 2, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="rounded-xl border p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-right">
                    <div>
                        <div class="text-sm text-gray-500">Total Bruto</div>
                        <div class="text-lg font-bold">
                            R$ {{ number_format((float) ($this->totalGross ?? 0), 2, ',', '.') }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Total Descontos</div>
                        <div class="text-lg font-bold">
                            R$ {{ number_format((float) ($this->totalDiscounts ?? 0), 2, ',', '.') }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Total Líquido</div>
                        <div class="text-lg font-bold">
                            R$ {{ number_format((float) ($this->totalNet ?? 0), 2, ',', '.') }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Total FGTS</div>
                        <div class="text-lg font-bold">
                            R$ {{ number_format((float) ($this->totalFgts ?? 0), 2, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="rounded-xl border p-6 text-center text-gray-500">
                Nenhum dado gerado ainda.
            </div>
        @endif
    </div>
</x-filament-panels::page>
