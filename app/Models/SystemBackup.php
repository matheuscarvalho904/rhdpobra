<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemBackup extends Model
{
    protected $fillable = [
        'name',
        'disk',
        'path',
        'status',
        'size',
        'message',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function getSizeForHumansAttribute(): string
    {
        if (! $this->size) {
            return '-';
        }

        return number_format($this->size / 1024 / 1024, 2, ',', '.') . ' MB';
    }
}