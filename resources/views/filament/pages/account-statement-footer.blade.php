<x-filament::section class="!p-0 overflow-hidden border-t-0 rounded-t-none mt-1 shadow-sm">
    {{-- Totals Row --}}
    <div style="display: grid; grid-template-columns: 1fr 100px 120px 120px 150px 150px; align-items: center; background: #fdfdfd; border-top: 1px solid #f3f4f6;" class="dark:bg-gray-800/80 dark:border-gray-700">
        <div style="grid-column: span 4; text-align: right; padding: 10px 24px; font-weight: 700; font-size: 0.75rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.025em;">
            {{ __('Total Equivalent (إجمالي المقابل)') }}
        </div>
        <div style="text-align: right; padding: 10px 16px; font-weight: 800; color: #4b5563; font-family: tabular-nums; border-inline-start: 1px solid #f3f4f6;" class="dark:text-white dark:border-gray-700">
            {{ number_format($total_mc_debit, 2) }}
        </div>
        <div style="text-align: right; padding: 10px 16px; font-weight: 800; color: #4b5563; font-family: tabular-nums; border-inline-start: 1px solid #f3f4f6;" class="dark:text-white dark:border-gray-700">
            {{ number_format($total_mc_credit, 2) }}
        </div>
    </div>
    
    {{-- Net Balance Bar --}}
    <div style="background: linear-gradient(to right, #d97706, #b45309); color: white; padding: 16px 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; flex-direction: column;">
                <span style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; opacity: 0.8;">
                    {{ __('Net Total Equivalent Balance') }}
                </span>
                <span style="font-size: 0.95rem; font-weight: 900;">
                    {{ __('صافي الرصيد الإجمالي بالمقابل') }}
                </span>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 2.25rem; font-weight: 900; font-family: tabular-nums; line-height: 1;">
                    {{ number_format($net_balance, 2) }}
                </span>
            </div>
        </div>
    </div>
</x-filament::section>
