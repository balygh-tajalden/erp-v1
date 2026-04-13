@php
    $currencyId = $group->getIdentifier();
    $openingBalance = $this->getOpeningBalance($currencyId);
    $status = $openingBalance >= 0 ? 'عليه' : 'له';
    $date = $this->data['from_date'] ?? now()->startOfMonth()->toDateString();
@endphp

<tr class="bg-gray-50/50">
    <td class="fi-ta-cell" colspan="2"></td>
    <td class="fi-ta-cell">
        <div class="flex items-center justify-center">
            <span class="fi-badge flex items-center justify-center gap-x-1 rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $status === 'عليه' ? 'bg-warning-50 text-warning-700 ring-warning-600/10' : 'bg-gray-50 text-gray-600 ring-gray-500/10' }}">
                {{ $status }}
            </span>
        </div>
    </td>
    <td class="fi-ta-cell px-4 py-3 text-sm font-bold">
        {{ number_format($openingBalance, 2) }}
    </td>
    <td class="fi-ta-cell px-4 py-3 text-sm">
        {{ $group->getLabel() }}
    </td>
    <td class="fi-ta-cell px-4 py-3 text-sm">
        {{ $date }}
    </td>
    <td class="fi-ta-cell" colspan="2"></td>
    <td class="fi-ta-cell px-4 py-3 text-sm text-gray-500">
        رصيد سابق
    </td>
</tr>
