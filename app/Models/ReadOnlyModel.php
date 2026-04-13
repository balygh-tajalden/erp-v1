<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Exception;

abstract class ReadOnlyModel extends Model
{
    /**
     * The primary key for the model.
     * Since views often don't have a standard ID behavior or it's non-incrementing.
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * Prevent saving data to the view.
     */
    public function save(array $options = [])
    {
        throw new Exception("Cannot save to a read-only view model.");
    }

    /**
     * Prevent updating data in the view.
     */
    public function update(array $attributes = [], array $options = [])
    {
        throw new Exception("Cannot update a read-only view model.");
    }

    /**
     * Prevent deleting data from the view.
     */
    public function delete()
    {
        throw new Exception("Cannot delete from a read-only view model.");
    }

    /**
     * Prevent static deletion.
     */
    public static function destroy($ids)
    {
        throw new Exception("Cannot destroy read-only view records.");
    }
}
