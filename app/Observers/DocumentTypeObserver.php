<?php

namespace App\Observers;

use App\Models\DocumentType;
use Illuminate\Support\Facades\Cache;

class DocumentTypeObserver
{
    protected function clearLookupCache(): void
    {
        Cache::forget('lookup:document_types');
    }
    /**
     * Handle the DocumentType "created" event.
     */
    public function created(DocumentType $documentType): void
    {
        $this->clearLookupCache();
    }

    /**
     * Handle the DocumentType "updated" event.
     */
    public function updated(DocumentType $documentType): void
    {
        $this->clearLookupCache();
    }

    /**
     * Handle the DocumentType "deleted" event.
     */
    public function deleted(DocumentType $documentType): void
    {
        $this->clearLookupCache();
    }

    /**
     * Handle the DocumentType "restored" event.
     */
    public function restored(DocumentType $documentType): void
    {
        $this->clearLookupCache();
    }

    /**
     * Handle the DocumentType "force deleted" event.
     */
    public function forceDeleted(DocumentType $documentType): void
    {
        $this->clearLookupCache();
    }
}
