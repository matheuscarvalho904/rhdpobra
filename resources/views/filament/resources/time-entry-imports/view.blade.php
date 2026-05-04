<div class="space-y-4">
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <strong>Empresa:</strong>
            <div>{{ $record->company?->name ?? '-' }}</div>
        </div>

        <div>
            <strong>Status:</strong>
            <div>{{ $record->status ?? '-' }}</div>
        </div>

        <div>
            <strong>Período:</strong>
            <div>
                {{ $record->start_date?->format('d/m/Y') ?? '-' }}
                até
                {{ $record->end_date?->format('d/m/Y') ?? '-' }}
            </div>
        </div>

        <div>
            <strong>Importado em:</strong>
            <div>{{ $record->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
        </div>

        <div>
            <strong>Total:</strong>
            <div>{{ $record->total_records }}</div>
        </div>

        <div>
            <strong>Importados:</strong>
            <div>{{ $record->imported_records }}</div>
        </div>

        <div>
            <strong>Ignorados:</strong>
            <div>{{ $record->ignored_records }}</div>
        </div>

        <div>
            <strong>Integração:</strong>
            <div>{{ $record->pointIntegration?->name ?? '-' }}</div>
        </div>
    </div>

    @if ($record->error_message)
        <div class="rounded-lg bg-danger-50 p-4 text-sm text-danger-700 dark:bg-danger-950 dark:text-danger-300">
            <strong>Erro:</strong>
            <div>{{ $record->error_message }}</div>
        </div>
    @endif

    <div>
        <strong>Metadata:</strong>

        <pre class="mt-2 max-h-96 overflow-auto rounded-lg bg-gray-950 p-4 text-xs text-gray-100">{{ json_encode($record->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
    </div>
</div>