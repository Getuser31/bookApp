<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use File;

class ClearLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear application logs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        File::put(storage_path('logs/laravel.log'), '');
        $this->info('Logs have been cleared!');
        return 0;
    }
}
