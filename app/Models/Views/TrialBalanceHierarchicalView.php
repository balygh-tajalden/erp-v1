<?php

namespace App\Models\Views;

use App\Models\ReadOnlyModel;

class TrialBalanceHierarchicalView extends ReadOnlyModel
{
    protected $table = 'vw_trialbalancehierarchical';

    protected $casts = [
        'AccountID' => 'integer',
        'OpeningBalance' => 'decimal:4',
        'PeriodMovement' => 'decimal:4',
        'FinalBalance' => 'decimal:4',
        'Level' => 'integer',
    ];
}
