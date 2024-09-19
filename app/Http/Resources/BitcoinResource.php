<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BitcoinResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'transaction' => [
                'amount' => $this->resource['transaction']->amount,
                'btc_amount' => $this->resource['transaction']->btc_amount,
                'btc_price_at_time' => $this->resource['transaction']->btc_price_at_time,
                'type' => $this->resource['transaction']->type,
                'status' => $this->resource['transaction']->status,
            ],
            'bitcoin_price' => $this->resource['bitcoin_price'],
        ];
    }
}
