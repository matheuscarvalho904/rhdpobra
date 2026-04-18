<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-4">
                <h2 class="text-xl font-bold tracking-tight text-gray-900">
                    Relatório de Pagamento de Adiantamentos
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Filtre os dados por empresa, filial, obra, período, status e forma de pagamento.
                </p>
            </div>

            <div class="px-6 py-6">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Empresa</label>
                        <select wire:model.live="company_id" class="w-full rounded-xl border-gray-300 shadow-sm">
                            <option value="">Todas</option>
                            @foreach ($this->companies as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Filial</label>
                        <select wire:model.live="branch_id" class="w-full rounded-xl border-gray-300 shadow-sm">
                            <option value="">Todas</option>
                            @foreach ($this->branches as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Obra</label>
                        <select wire:model.live="work_id" class="w-full rounded-xl border-gray-300 shadow-sm">
                            <option value="">Todas</option>
                            @foreach ($this->works as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Status</label>
                        <select wire:model.live="status" class="w-full rounded-xl border-gray-300 shadow-sm">
                            <option value="">Todos</option>
                            <option value="draft">Rascunho</option>
                            <option value="paid">Pago</option>
                            <option value="canceled">Cancelado</option>
                            <option value="integrated_payroll">Integrado na Folha</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Data Inicial</label>
                        <input type="date" wire:model.live="date_from" class="w-full rounded-xl border-gray-300 shadow-sm">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Data Final</label>
                        <input type="date" wire:model.live="date_to" class="w-full rounded-xl border-gray-300 shadow-sm">
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-gray-700">Pagamento</label>
                        <select wire:model.live="payment_method" class="w-full rounded-xl border-gray-300 shadow-sm">
                            <option value="">Todos</option>
                            <option value="pix">PIX</option>
                            <option value="bank_transfer">Transferência</option>
                            <option value="cash">Dinheiro</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <x-filament::button wire:click="generateReport" size="md">
                        Gerar Relatório
                    </x-filament::button>

                    <x-filament::button color="success" wire:click="exportPdf" size="md" icon="heroicon-m-document-arrow-down">
                        Exportar PDF
                    </x-filament::button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-medium text-gray-500">Período</div>
                <div class="mt-2 text-base font-bold text-gray-900">
                    {{ $this->date_from ? \Carbon\Carbon::parse($this->date_from)->format('d/m/Y') : '--' }}
                    até
                    {{ $this->date_to ? \Carbon\Carbon::parse($this->date_to)->format('d/m/Y') : '--' }}
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-medium text-gray-500">Forma de Pagamento</div>
                <div class="mt-2 text-base font-bold text-gray-900">
                    @switch($this->payment_method)
                        @case('pix') PIX @break
                        @case('bank_transfer') Transferência @break
                        @case('cash') Dinheiro @break
                        @default Todos
                    @endswitch
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-sm font-medium text-gray-500">Total Geral</div>
                <div class="mt-2 text-2xl font-bold text-primary-600">
                    R$ {{ number_format($this->totalAmount, 2, ',', '.') }}
                </div>
            </div>
        </div>

        <div class="space-y-6">
            @forelse ($this->rows as $company => $works)
                <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="text-lg font-bold text-gray-900">
                            Empresa: {{ $company }}
                        </h3>
                    </div>

                    <div class="px-6 py-6 space-y-6">
                        @foreach ($works as $work => $data)
                            <div class="rounded-2xl border border-gray-200 overflow-hidden">
                                <div class="flex items-center justify-between bg-gray-50 px-4 py-3">
                                    <h4 class="text-sm font-bold uppercase tracking-wide text-gray-700">
                                        Obra: {{ $work }}
                                    </h4>
                                    <div class="text-sm font-semibold text-gray-700">
                                        Total da Obra:
                                        <span class="text-gray-900">R$ {{ number_format($data['total'], 2, ',', '.') }}</span>
                                    </div>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="px-4 py-3 text-left font-bold text-gray-700">Colaborador</th>
                                                <th class="px-4 py-3 text-left font-bold text-gray-700">Matrícula</th>
                                                <th class="px-4 py-3 text-left font-bold text-gray-700">Cargo</th>
                                                <th class="px-4 py-3 text-center font-bold text-gray-700">Data</th>
                                                <th class="px-4 py-3 text-left font-bold text-gray-700">Pagamento</th>
                                                <th class="px-4 py-3 text-left font-bold text-gray-700">Tipo PIX</th>
                                                <th class="px-4 py-3 text-left font-bold text-gray-700">Chave PIX</th>
                                                <th class="px-4 py-3 text-left font-bold text-gray-700">Documento</th>
                                                <th class="px-4 py-3 text-left font-bold text-gray-700">Status</th>
                                                <th class="px-4 py-3 text-right font-bold text-gray-700">Valor</th>
                                            </tr>
                                        </thead>

                                        <tbody class="divide-y divide-gray-100 bg-white">
                                            @foreach ($data['rows'] as $row)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $row['employee_name'] }}</td>
                                                    <td class="px-4 py-3 text-gray-700">{{ $row['code'] ?: '-' }}</td>
                                                    <td class="px-4 py-3 text-gray-700">{{ $row['job_role'] ?: '-' }}</td>
                                                    <td class="px-4 py-3 text-center text-gray-700">{{ $row['advance_date'] }}</td>
                                                    <td class="px-4 py-3 text-gray-700">{{ $row['payment_method'] }}</td>
                                                    <td class="px-4 py-3 text-gray-700">{{ $row['pix_key_type'] ?: '-' }}</td>
                                                    <td class="px-4 py-3 text-gray-700">{{ $row['pix_key'] ?: '-' }}</td>
                                                    <td class="px-4 py-3 text-gray-700">{{ $row['pix_holder_document'] ?: '-' }}</td>
                                                    <td class="px-4 py-3 text-gray-700">{{ $row['status'] }}</td>
                                                    <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                                        R$ {{ number_format($row['amount'], 2, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>

                                        <tfoot class="bg-gray-50">
                                            <tr>
                                                <td colspan="9" class="px-4 py-3 text-right text-sm font-bold text-gray-700">
                                                    Total da Obra
                                                </td>
                                                <td class="px-4 py-3 text-right text-sm font-bold text-primary-700">
                                                    R$ {{ number_format($data['total'], 2, ',', '.') }}
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-12 text-center">
                    <div class="text-base font-semibold text-gray-700">Nenhum dado gerado ainda.</div>
                    <div class="mt-1 text-sm text-gray-500">
                        Ajuste os filtros e clique em <strong>Gerar Relatório</strong>.
                    </div>
                </div>
            @endforelse
        </div>

        <div class="rounded-2xl border border-primary-200 bg-primary-50 p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-base font-semibold text-primary-900">Total Geral do Relatório</span>
                <span class="text-2xl font-bold text-primary-700">
                    R$ {{ number_format($this->totalAmount, 2, ',', '.') }}
                </span>
            </div>
        </div>
    </div>
</x-filament-panels::page>