<?php

namespace App\Console\Commands;

use App\Models\BtcQuote;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PurgeOldBitcoinPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:purge-old-bitcoin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove preços de Bitcoin com mais de 90 dias';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoffDate = Carbon::now()->subDays(90);

        BtcQuote::where('recorded_at', '<', $cutoffDate)->delete();

        $this->info('Preços antigos de Bitcoin removidos com sucesso!');
    }
}
