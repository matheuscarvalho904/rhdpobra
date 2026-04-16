<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            {{ $this->form }}
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Colaboradores em Aviso Prévio</h2>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full border border-gray-200 text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-3 py-2 text-left">Código</th>
                            <th class="border px-3 py-2 text-left">Colaborador</th>
                            <th class="border px-3 py-2 text-left">Empresa</th>
                            <th class="border px-3 py-2 text-left">Filial</th>
                            <th class="border px-3 py-2 text-left">Obra</th>
                            <th class="border px-3 py-2 text-left">Cargo</th>
                            <th class="border px-3 py-2 text-left">Contrato</th>
                            <th class="border px-3 py-2 text-left">Matrícula Atual</th>
                            <th class="border px-3 py-2 text-left">Admissão</th>
                            <th class="border px-3 py-2 text-right">Salário</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->reportData as $employee)
                            <tr>
                                <td class="border px-3 py-2">{{ $employee['code'] ?: '-' }}</td>
                                <td class="border px-3 py-2">{{ $employee['name'] ?: '-' }}</td>
                                <td class="border px-3 py-2">{{ $employee['company_name'] ?: '-' }}</td>
                                <td class="border px-3 py-2">{{ $employee['branch_name'] ?: '-' }}</td>
                                <td class="border px-3 py-2">{{ $employee['work_name'] ?: '-' }}</td>
                                <td class="border px-3 py-2">{{ $employee['job_role_name'] ?: '-' }}</td>
                                <td class="border px-3 py-2">{{ $employee['contract_type_name'] ?: '-' }}</td>
                                <td class="border px-3 py-2">{{ $employee['registration_number'] ?: '-' }}</td>
                                <td class="border px-3 py-2">{{ $employee['admission_date'] ?: '-' }}</td>
                                <td class="border px-3 py-2 text-right">
                                    R$ {{ number_format((float) ($employee['salary'] ?? 0), 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="border px-3 py-4 text-center text-gray-500">
                                    Nenhum colaborador em aviso prévio encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>