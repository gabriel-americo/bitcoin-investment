<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InvestmentService;

class InvestmentController extends Controller
{
    public function __construct(
        protected InvestmentService $investmentService
    ) {}

    public function position()
    {
        $result = $this->investmentService->getInvestmentPosition();

        if ($result['status'] == 'error') {
            return response()->json(['message' => $result['message']], 404);
        }

        return response()->json([
            'message' => 'Posição dos investimentos recuperada com sucesso!',
            'investments' => $result['investments'],
            'btc_preco_atual' => $result['btc_preco_atual'],
        ], 200);
    }
}
