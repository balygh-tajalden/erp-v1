<?php

namespace App\Events\Accounting;

use App\Models\Entry;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JournalEntryPosted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Entry $entry
    ) {}
}
