<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DatabaseBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--keep=5 : Số lượng bản backup mới nhất cần lưu giữ}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tạo bản sao lưu cơ sở dữ liệu SQLite vào storage/app/backups và dọn dẹp các bản sao lưu cũ';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dbPath = database_path('database.sqlite');

        if (! File::exists($dbPath)) {
            $this->error("Không tìm thấy file CSDL tại: {$dbPath}");

            return Command::FAILURE;
        }

        $backupDir = storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupFileName = "database_backup_{$timestamp}.sqlite";
        $backupFilePath = "{$backupDir}/{$backupFileName}";

        if (! File::copy($dbPath, $backupFilePath)) {
            $this->error('Sao lưu thất bại: Không thể sao chép file CSDL.');

            return Command::FAILURE;
        }

        $sizeKb = round(File::size($backupFilePath) / 1024, 2);
        $this->info("✓ Đã tạo bản sao lưu thành công: {$backupFileName} ({$sizeKb} KB)");
        $this->line("  Đường dẫn: {$backupFilePath}");

        // Dọn dẹp các bản backup cũ
        $keep = max(1, (int) $this->option('keep'));
        $this->cleanOldBackups($backupDir, $keep);

        return Command::SUCCESS;
    }

    /**
     * Clean old backups exceeding the keep limit.
     */
    protected function cleanOldBackups(string $backupDir, int $keep): void
    {
        $files = File::files($backupDir);

        // Filter sqlite backup files
        $backupFiles = array_filter($files, function ($file) {
            return str_starts_with($file->getFilename(), 'database_backup_')
                && $file->getExtension() === 'sqlite';
        });

        // Sort descending by modified time (newest first)
        usort($backupFiles, function ($a, $b) {
            return $b->getMTime() <=> $a->getMTime();
        });

        $totalBackups = count($backupFiles);

        if ($totalBackups > $keep) {
            $filesToDelete = array_slice($backupFiles, $keep);
            $deletedCount = 0;

            foreach ($filesToDelete as $file) {
                File::delete($file->getPathname());
                $deletedCount++;
            }

            $this->comment("✓ Đã dọn dẹp {$deletedCount} bản sao lưu cũ (giữ lại {$keep} bản mới nhất).");
        } else {
            $this->comment("  Hiện có {$totalBackups}/{$keep} bản sao lưu được lưu trữ.");
        }
    }
}
