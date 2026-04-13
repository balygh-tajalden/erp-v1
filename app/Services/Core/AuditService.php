<?php

namespace App\Services\Core;

use App\Models\History;
use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;

/**
 * AuditService: Handles detailed data change logging (tblhistory).
 * Used to track "Old Data" vs "New Data" for critical entities.
 */
class AuditService extends BaseService
{
    /**
     * Log a data change event
     */
    public function logChange($tableName, $recordId, $operationId, $oldData = null, $newData = null, $notes = null)
    {
        return History::create([
            'TableName'   => $tableName,
            'RecordID'    => $recordId,
            'OperationID' => $operationId,
            'ChangeDate'  => now(),
            'ChangedBy'   => Auth::id(),
            'OldData'     => $oldData ? json_encode($oldData) : null,
            'NewData'     => $newData ? json_encode($newData) : null,
            'Notes'       => $notes,
            'MachineName' => request()->header('User-Agent'),
            'OSUserName'  => gethostname(),
        ]);
    }

    /**
     * Helper for 'Update' operations
     */
    public function logUpdate($model, $oldData, $notes = null)
    {
        return $this->logChange(
            $model->getTable(),
            $model->ID,
            2, // Operation ID for Update
            $oldData,
            $model->getAttributes(),
            $notes
        );
    }
}
