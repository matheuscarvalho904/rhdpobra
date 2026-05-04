<div class="space-y-4">
    <div>
        <strong>Status:</strong> {{ $record->status }}
    </div>

    <div>
        <strong>Disco:</strong> {{ $record->disk }}
    </div>

    <div>
        <strong>Arquivo:</strong> {{ $record->path ?: '-' }}
    </div>

    <div>
        <strong>Tamanho:</strong> {{ $record->size_for_humans }}
    </div>

    <div>
        <strong>Iniciado em:</strong>
        {{ $record->started_at ? $record->started_at->format('d/m/Y H:i:s') : '-' }}
    </div>

    <div>
        <strong>Finalizado em:</strong>
        {{ $record->finished_at ? $record->finished_at->format('d/m/Y H:i:s') : '-' }}
    </div>

    <div>
        <strong>Mensagem:</strong>

        <pre style="white-space: pre-wrap; font-size: 12px; margin-top: 8px;">{{ $record->message ?: 'Sem mensagem.' }}</pre>
    </div>
</div>