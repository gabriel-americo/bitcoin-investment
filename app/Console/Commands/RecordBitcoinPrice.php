<?php

namespace App\Console\Commands;

use App\Models\BtcQuote;
use App\Services\BitcoinService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RecordBitcoinPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:record-bitcoin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grava o preço de compra e venda do Bitcoin a cada 10 minutos';

    protected $bitcoinService;

    public function __construct(BitcoinService $bitcoinService)
    {
        parent::__construct();
        $this->bitcoinService = $bitcoinService;
    }
    
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $buyPrice = $this->bitcoinService->getBitcoinPrice('buy');
        $sellPrice = $this->bitcoinService->getBitcoinPrice('sell');
        
        BtcQuote::create([
            'buy_price' => $buyPrice,
            'sell_price' => $sellPrice,
            'recorded_at' => Carbon::now(),
        ]);

        $this->info('Preço do Bitcoin registrado com sucesso!');
    }
}
