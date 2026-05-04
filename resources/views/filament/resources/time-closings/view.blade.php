<div class="space-y-4">

    <div class="grid grid-cols-2 gap-4">
        <div>
            <strong>Empresa:</strong>
            <div>{{ $record->company?->name }}</div>
        </div>

        <div>
            <strong>Status:</strong>
            <div>{{ $record->status }}</div>
        </div>

        <div>
            <strong>Período:</strong>
            <div>
                {{ $record->start_date->format('d/m/Y') }}
                até
                {{ $record->end_date->format('d/m/Y') }}
            </div>
        </div>

        <div>
            <strong>Colaboradores:</strong>
            <div>{{ $record->employee_count }}</div>
        </div>

        <div>
            <strong>Horas Trabalhadas:</strong>
            <div>{{ $record->total_worked_hours }}</div>
        </div>
    </div>

</div>