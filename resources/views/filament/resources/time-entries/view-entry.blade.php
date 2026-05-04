<div class="space-y-4">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <strong>Empresa:</strong>
            <div>{{ $record->company?->name ?? '-' }}</div>
        </div>

        <div>
            <strong>Colaborador:</strong>
            <div>{{ $record->employee?->name ?? 'Não vinculado' }}</div>
        </div>

        <div>
            <strong>Data:</strong>
            <div>{{ $record->entry_date?->format('d/m/Y') ?? '-' }}</div>
        </div>

        <div>
            <strong>Marcação:</strong>
            <div>{{ $record->entry_datetime?->format('d/m/Y H:i:s') ?? '-' }}</div>
        </div>

        <div>
            <strong>Tipo:</strong>
            <div>{{ $record->type ?? '-' }}</div>
        </div>

        <div>
            <strong>Status:</strong>
            <div>{{ $record->status ?? '-' }}</div>
        </div>

        <div>
            <strong>Origem:</strong>
            <div>{{ $record->provider ?? '-' }}</div>
        </div>

        <div>
            <strong>Fonte:</strong>
            <div>{{ $record->source ?? '-' }}</div>
        </div>

        <div>
            <strong>ID Externo Colaborador:</strong>
            <div>{{ $record->external_employee_id ?? '-' }}</div>
        </div>

        <div>
            <strong>ID Externo Marcação:</strong>
            <div>{{ $record->external_id ?? '-' }}</div>
        </div>

        <div>
            <strong>Importação:</strong>
            <div>#{{ $record->time_entry_import_id ?? '-' }}</div>
        </div>

        <div>
            <strong>Item Importado:</strong>
            <div>#{{ $record->time_entry_import_item_id ?? '-' }}</div>
        </div>
    </div>

    @if ($record->notes)
        <div>
            <strong>Observações:</strong>
            <div>{{ $record->notes }}</div>
        </div>
    @endif

    <div>
        <strong>Payload Original:</strong>

        <pre class="mt-2 max-h-96 overflow-auto rounded-lg bg-gray-950 p-4 text-xs text-gray-100">{{ json_encode($record->raw_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
    </div>
</div>