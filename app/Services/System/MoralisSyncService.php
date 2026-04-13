<?php

namespace App\Services\System;

use App\Models\WalletTransaction;
use App\Models\Currency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\AccountWallet;
use App\Services\System\WhatsAppService;
use Carbon\Carbon;

class MoralisSyncService
{
    protected string $usdtContract = '0x55d398326f99059ff775485246999027b3197955';
    protected string $chain = 'bsc';

    public function sync($wallet, array $params = [])
    {
        $apiKey = config('services.moralis.api_key');
        if (!$wallet || !$apiKey) return ['count' => 0, 'last_block' => null];

        $wallet = strtolower($wallet);
        $cursor = null;
        $maxPages = 40;
        $currentPage = 0;
        $newRecordsCount = 0;
        $lastScannedBlock = null;

        try {
            do {
                $currentParams = array_merge([
                    'chain' => $this->chain,
                    'order' => 'DESC',
                    'limit' => 100,
                ], $params);
                
                if ($cursor) $currentParams['cursor'] = $cursor;

                $response = Http::withHeaders([
                    'X-API-Key' => $apiKey,
                    'accept' => 'application/json',
                ])->timeout(30)->get("https://deep-index.moralis.io/api/v2.2/{$wallet}/erc20/transfers", $currentParams);

                if (!$response->successful()) break;

                $pageData = $response->json();
                $pageTransfers = $pageData['result'] ?? [];
                
                if (count($pageTransfers) > 0 && !$lastScannedBlock) {
                    $lastScannedBlock = $pageTransfers[0]['block_number'];
                }

                $currencyId = Currency::where('ISO_Code', 'USDT')
                    ->orWhere('CurrencyName', 'LIKE', '%تيثر%')
                    ->value('ID') ?? 4;

                foreach ($pageTransfers as $transfer) {
                    if (strtolower($transfer['address'] ?? '') !== $this->usdtContract) continue;
                    
                    $amount = floatval($transfer['value_decimal'] ?? 0);
                    if ($amount < 1) continue;

                    $isIngoing = strtolower($transfer['to_address']) === $wallet;

                    WalletTransaction::updateOrCreate(
                        ['TransactionHash' => $transfer['transaction_hash'], 'LogIndex' => $transfer['log_index'] ?? 0],
                        [
                            'WalletAddress'  => $wallet,
                            'BlockNumber'    => $transfer['block_number'],
                            'BlockTimestamp' => Carbon::parse($transfer['block_timestamp']),
                            'FromAddress'    => strtolower($transfer['from_address']),
                            'ToAddress'      => strtolower($transfer['to_address']),
                            'Amount'         => $amount,
                            'TokenAddress'   => strtolower($transfer['address']),
                            'TokenSymbol'    => $transfer['token_symbol'],
                            'TokenName'      => $transfer['token_name'] ?? 'USDT',
                            'IsIngoing'      => $isIngoing,
                            'CurrencyID'     => $currencyId,
                            'Chain'          => $this->chain,
                            'BranchID'       => 2, 
                        ]
                    );

                    $newRecordsCount++;
                }
                
                $cursor = $pageData['cursor'] ?? null;
                $currentPage++;
            } while ($cursor && $currentPage < $maxPages && count($pageTransfers) >= 100);

            Log::info("Moralis Sync: {$newRecordsCount} records processed for {$wallet}.");
            return ['count' => $newRecordsCount, 'last_block' => $lastScannedBlock];

        } catch (\Exception $e) {
            Log::error('Moralis Sync Error: ' . $e->getMessage());
            return ['count' => 0, 'last_block' => null];
        }
    }

    public function syncNewTransactions($wallet)
    {
        $latestRecord = WalletTransaction::where('WalletAddress', $wallet)
            ->orderBy('BlockTimestamp', 'desc')
            ->first();

        $params = [];
        if ($latestRecord && $latestRecord->BlockNumber) {
            $params['from_block'] = $latestRecord->BlockNumber + 1;
        }

        return $this->sync($wallet, $params);
    }

    public function syncOlderTransactions($wallet)
    {
        $oldestRecord = WalletTransaction::where('WalletAddress', $wallet)
            ->orderBy('BlockTimestamp', 'asc')
            ->first();

        $lastScannedBlock = Cache::get("sync_old_block_{$wallet}");
        $params = [];

        if ($oldestRecord) {
            $toBlock = $oldestRecord->BlockNumber - 1;
            if ($lastScannedBlock && $lastScannedBlock < $toBlock) {
                $toBlock = $lastScannedBlock;
            }
            $params['to_block'] = $toBlock;
        }

        $result = $this->sync($wallet, $params);
        
        if ($result['last_block']) {
            Cache::put("sync_old_block_{$wallet}", $result['last_block'] - 1, now()->addHours(1));
        }

        return $result;
    }
}
