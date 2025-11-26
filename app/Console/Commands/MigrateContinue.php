<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MigrateContinue extends Command
{
    protected $signature = 'migrate:continue {--dir=database/migrations} {--force}';
    protected $description = 'Run migrations one-by-one and continue on error';

    public function handle(): int
    {
        $dir = base_path($this->option('dir'));
        if (!is_dir($dir)) {
            $this->error("Directory not found: $dir");
            return 1;
        }

        $files = glob($dir . '/*.php');
        sort($files);

        $failed = [];

        foreach ($files as $file) {
            $rel = str_starts_with($file, base_path())
                ? substr($file, strlen(base_path()) + 1)
                : $file;

            $this->line("→ $rel");

            try {
                $code = Artisan::call('migrate', [
                    '--path'     => $file,     // absolute
                    '--realpath' => true,
                    '--force'    => (bool)$this->option('force'),
                ]);

                $out = trim(Artisan::output());
                if ($out !== '') {
                    $this->line($out);
                }

                if ($code !== 0) {
                    $this->warn("   Skipped (exit code $code)");
                    $failed[] = $rel;
                }
            } catch (\Throwable $e) {
                $this->error('   Error: ' . $e->getMessage());
                $failed[] = $rel;
            }

            $this->newLine();
        }

        if ($failed) {
            $this->warn('Failed/skipped migrations:');
            foreach ($failed as $f) {
                $this->warn(" - $f");
            }
            return 1;
        }

        $this->info('All migrations attempted.');
        return 0;
    }
}
