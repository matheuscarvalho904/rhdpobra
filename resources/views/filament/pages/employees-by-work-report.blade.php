<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            {{ $this->form }}
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-gray-900">Relatório por Obra</h2>

            <div class="mt-4 space-y-6">
                @forelse($this->reportData as $workName => $data)
                    <div class="rounded-lg border border-gray-200 p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-base font-semibold text-gray-900">{{ $workName }}</h3>
                            <span class="text-sm font-medium text-gray-600">
                                Total: {{ $data['total'] ?? 0 }}
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-200 text-sm">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border px-3 py-2 text-left">Código</th>
                                        <th class="border px-3 py-2 text-left">Colaborador</th>
                                        <th class="border px-3 py-2 text-left">Cargo</th>
                                        <th class="border px-3 py-2 text-left">Contrato</th>
                                        <th class="border px-3 py-2 text-left">Status</th>
                                        <th class="border px-3 py-2 text-left">Admissão</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(($data['employees'] ?? []) as $employee)
                                        <tr>
                                            <td class="border px-3 py-2">{{ $employee['code'] ?? '-' }}</td>
                                            <td class="border px-3 py-2">{{ $employee['name'] ?? '-' }}</td>
                                            <td class="border px-3 py-2">{{ $employee['job_role_name'] ?? '-' }}</td>
                                            <td class="border px-3 py-2">{{ $employee['contract_type_name'] ?? '-' }}</td>
                                            <td class="border px-3 py-2">{{ $employee['status'] ?? '-' }}</td>
                                            <td class="border px-3 py-2">{{ $employee['admission_date'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Nenhum colaborador encontrado.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-filament-panels::page>