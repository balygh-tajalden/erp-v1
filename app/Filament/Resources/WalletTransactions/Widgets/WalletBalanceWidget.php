<?php

namespace App\Filament\Resources\WalletTransactions\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\WalletTransaction;
use Livewire\Attributes\On;

class WalletBalanceWidget extends BaseWidget
{
    public ?string $address = null;

    protected function getStats(): array
    {
        $apiBalance = $this->getApiBalance();
        $dbBalance = $this->getDatabaseBalance();
        $diff = abs($apiBalance - $dbBalance);

        return [
            Stat::make('Live USDT Balance (API)', number_format($apiBalance, 2) . ' USDT')
                ->description('Balance at last sync')
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color('success'),

            Stat::make('System Balance (Database)', number_format($dbBalance, 2) . ' USDT')
                ->description('Calculated from local records')
                ->descriptionIcon('heroicon-m-circle-stack')
                ->color($diff < 0.01 ? 'success' : 'warning'),
        ];
    }

    protected function getDatabaseBalance(): float
    {
        /** @var \App\Services\AccountingService $accountingService */
        $accountingService = app(\App\Services\AccountingService::class);
        
        // Account ID 243 is "صندوق ترست"
        $balance = $accountingService->getAccountBalance(243);

        // Accounting balance uses Negative for Debit and Positive for Credit
        // For a Cash/Fund account, Debit (Negative) is the actual positive balance
        return (float) abs($balance);
    }

    protected function getApiBalance(): float
    {
        $address = $this->address ?? '0x4337232444a07Cfe58aF6258509C350663946cB1';
        // Get from cache without expiration unless forced
        return (float) Cache::get("wallet_api_balance_{$address}", 0.0);
    }

    #[On('refreshWalletStats')]
    public function refreshStats(): void
    {
        $address = $this->address ?? '0x4337232444a07Cfe58aF6258509C350663946cB1';
        $apiKey = config('services.moralis.api_key');
        $usdtContract = '0x55d398326f99059ff775485246999027b3197955';

        if (!$apiKey) return;

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'accept' => 'application/json',
            ])->timeout(15)->get("https://deep-index.moralis.io/api/v2.2/{$address}/erc20", [
                'chain' => 'bsc',
                'token_addresses' => [$usdtContract],
            ]);

            if ($response->successful()) {
                $tokens = $response->json();
                $balance = 0.0;
                foreach ($tokens as $token) {
                    if (strtolower($token['token_address'] ?? '') === strtolower($usdtContract)) {
                        $balance = floatval($token['balance'] / pow(10, $token['decimals']));
                        break;
                    }
                }
                // Store in cache indefinitely until next refresh
                Cache::forever("wallet_api_balance_{$address}", $balance);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("WalletBalanceWidget Refresh Error: " . $e->getMessage());
        }
    }
}
