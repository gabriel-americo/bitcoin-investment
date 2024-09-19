<?php

namespace App\Services;

use App\Mail\ConfirmationTransactionDepositMail;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TransactionService
{
    protected $user;

    public function __construct()
    {
        $this->user = Auth::user();
    }

    public function deposit(float $amount)
    {
        if ($amount <= 0) {
            throw new \Exception('O valor do depósito deve ser positivo.');
        }

        DB::beginTransaction();

        try {
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $this->user->id],
                ['balance_reais' => 0, 'balance_btc' => 0]
            );

            $transaction = Transaction::create([
                'amount' => $amount,
                'type' => 'deposit',
                'status' => 'completed',
                'user_id' => $this->user->id,
                'wallet_id' => $wallet->id,
            ]);

            $wallet->balance_reais += $amount;
            $wallet->save();

            $formattedAmount = 'R$ ' . number_format($amount, 2, ',', '.');

            try {
                Mail::to($this->user->email)->send(new ConfirmationTransactionDepositMail($this->user, $formattedAmount));
            } catch (\Exception $e) {
                Log::error('Erro ao enviar o email de confirmação de depósito: ' . $e->getMessage());
            }

            DB::commit();

            return $transaction;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar o depósito: ' . $e->getMessage());
            throw new \Exception('Erro ao processar o depósito: ' . $e->getMessage());
        }
    }

    public function withdrawal(float $amount)
    {
        $wallet = Wallet::where('user_id', $this->user->id)->firstOrFail();

        if ($wallet->balance_reais < $amount) {
            throw new \Exception('Saldo insuficiente!');
        }

        $transaction = Transaction::create([
            'amount' => $amount,
            'type' => 'withdrawal',
            'status' => 'completed',
            'user_id' => $this->user->id,
            'wallet_id' => $wallet->id,
        ]);

        $wallet->balance_reais -= $amount;
        $wallet->save();

        return $transaction;
    }

    public function extract($startDate = null, $endDate = null)
    {
        $startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::now()->subDays(90)->startOfDay();
        $endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::now()->endOfDay();

        $transactions = Transaction::where('user_id', $this->user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($transactions->isEmpty()) {
            return [
                'message' => 'Não há extrato disponível no intervalo de datas fornecido.',
                'transactions' => [],
                'start_date' => $startDate->format('d/m/Y'),
                'end_date' => $endDate->format('d/m/Y')
            ];
        }

        $transactionDetails = $transactions->map(function ($transaction) {
            return [
                'type' => $transaction->type,
                'amount' => 'R$ ' . number_format($transaction->amount, 2, ',', '.'),
                'btc_amount' => $transaction->btc_amount ? number_format($transaction->btc_amount, 8) . ' BTC' : null,
                'btc_price_at_time' => $transaction->btc_price_at_time ? 'R$ ' . number_format($transaction->btc_price_at_time, 2, ',', '.') : null,
                'date' => $transaction->created_at->format('d/m/Y H:i'),
                'status' => $transaction->status,
            ];
        });

        return [
            'transactions' => $transactionDetails,
            'start_date' => $startDate->format('d/m/Y'),
            'end_date' => $endDate->format('d/m/Y')
        ];
    }

    public function bitcoinVolume()
    {
        $today = Carbon::now()->startOfDay();

        $btcPurchased = Transaction::where('type', 'purchase')
            ->whereDate('created_at', $today)
            ->sum('btc_amount');

        $btcSold = Transaction::where('type', 'sale')
            ->whereDate('created_at', $today)
            ->sum('btc_amount');

        return [
            'btc_purchased' => $btcPurchased,
            'btc_sold' => $btcSold,
        ];
    }
}
