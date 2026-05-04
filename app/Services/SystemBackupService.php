<?php

namespace App\Services;

use App\Models\SystemBackup;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SystemBackupService
{
    public function run(): SystemBackup
    {
        $disk = env('BACKUP_DISK', 'local');

        $backup = SystemBackup::create([
            'name' => 'Backup - ' . now()->format('d/m/Y H:i:s'),
            'disk' => $disk,
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            Artisan::call('backup:run', [
                '--only-db' => true,
                '--disable-notifications' => true,
            ]);

            $path = $this->findLatestBackup($disk);

            $message = trim(Artisan::output()) ?: 'Backup gerado com sucesso.';

            $backup->update([
                'status' => 'success',
                'path' => $path,
                'size' => $path ? Storage::disk($disk)->size($path) : null,
                'message' => $this->sanitizeMessage($message),
                'finished_at' => now(),
            ]);
        } catch (Throwable $e) {
            $backup->update([
                'status' => 'failed',
                'message' => $this->sanitizeMessage($e->getMessage()),
                'finished_at' => now(),
            ]);

            report($e);
        }

        return $backup;
    }

    protected function findLatestBackup(string $disk): ?string
    {
        $backupName = config('backup.backup.name');

        return collect(Storage::disk($disk)->allFiles($backupName))
            ->filter(fn (string $file): bool => str_ends_with($file, '.zip'))
            ->sortByDesc(fn (string $file): int => Storage::disk($disk)->lastModified($file))
            ->first();
    }

    protected function sanitizeMessage(?string $message): string
    {
        $message = (string) $message;

        if ($message === '') {
            return 'Sem mensagem.';
        }

        $converted = @mb_convert_encoding($message, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');

        $converted = preg_replace('/[^\P{C}\r\n\t]/u', '', $converted);

        return $converted ?: 'Mensagem não pôde ser convertida para UTF-8.';
    }
}