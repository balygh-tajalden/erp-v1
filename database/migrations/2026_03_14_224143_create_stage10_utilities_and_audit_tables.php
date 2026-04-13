<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Stage 10: Utilities & Audit Tables

        Schema::create('tblhistory', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('TableName', 255)->nullable();
            $table->bigInteger('RecordID')->nullable();
            $table->bigInteger('ChangedBy')->nullable();
            $table->dateTime('ChangeDate')->nullable();
            $table->text('OldData')->nullable();
            $table->text('NewData')->nullable();
            $table->string('Notes', 500)->nullable();
            $table->string('MachineName', 250)->nullable();
            $table->string('OSUserName', 250)->nullable();
            $table->integer('OperationID')->nullable();
            $table->string('FormName', 100)->nullable();
            $table->integer('ActionType')->nullable();
            $table->text('ActionDescription')->nullable();
            $table->dateTime('ActionDate')->nullable();
            $table->integer('UserID')->nullable();
            $table->integer('BranchID')->nullable();
            $table->integer('SessionID')->nullable();
        });

        Schema::create('BackupSettings', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('BackupPath', 500);
            $table->boolean('IsAutoBackupEnabled')->default(0);
            $table->time('AutoBackupTime');
            $table->integer('RetentionDays');
            $table->dateTime('CreatedDate')->useCurrent();
            $table->dateTime('ModifiedDate')->useCurrent();
            $table->integer('BackupInterval');
            $table->integer('BackupIntervalMinutes');
            $table->string('ManualBackupPath', 500)->nullable();
        });

        Schema::create('tblSystemEvents', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('SessionID');
            $table->string('EventType', 50);
            $table->string('DocumentName', 100)->nullable();
            $table->integer('RecordID')->nullable();
            $table->string('EventDescription', 500)->nullable();
            $table->dateTime('EventDate')->nullable();
            $table->integer('UserID');
            $table->integer('BranchID');
        });

        Schema::create('tblDatabaseDocumentation', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('TableName', 255);
            $table->string('TableDescription', 1000);
            $table->dateTime('CreatedDate')->nullable();
        });

        Schema::create('tblLockProsesses', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->binary('RowVersion', 8)->nullable();
            $table->bigInteger('PrevRowVersion')->nullable();
            $table->bigInteger('TheNumber')->nullable();
            $table->dateTime('LockDate')->nullable();
            $table->string('Notes', 255)->nullable();
            $table->bigInteger('UserID')->nullable();
            $table->bigInteger('BranchID')->nullable();
            $table->dateTime('EnterTime')->nullable();
            $table->bigInteger('SessionID')->nullable();
        });

        Schema::create('tblSystemUpdateLogs', function (Blueprint $table) {
            $table->increments('ID');
            $table->integer('UpdateID');
            $table->dateTime('ExecutionDate');
            $table->string('ExecutedBy', 255);
            $table->string('ExecutionStatus', 50);
            $table->integer('ExecutionTime')->nullable();
            $table->integer('AffectedRows')->nullable();
            $table->text('ErrorMessage')->nullable();
            $table->text('AppliedChanges')->nullable();
            $table->text('Warnings')->nullable();
        });

        Schema::create('tblSystemUpdates', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('ScriptName', 255);
            $table->longText('ScriptContent');
            $table->string('Description', 1000)->nullable();
            $table->string('Version', 50)->nullable();
            $table->dateTime('CreatedDate');
            $table->boolean('IsExecuted');
            $table->dateTime('ExecutedDate')->nullable();
            $table->string('ExecutedBy', 255)->nullable();
            $table->longText('ExecutionResult')->nullable();
            $table->string('ScriptType', 50)->nullable();
            $table->integer('ExecutionOrder');
            $table->string('Dependencies', 500)->nullable();
            $table->longText('RollbackScript')->nullable();
        });

        Schema::create('tblSystemWindows', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('WindowName', 255);
            $table->string('WindowCode', 50);
            $table->string('WindowDescription', 500)->nullable();
            $table->boolean('IsActive')->nullable();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedDate')->nullable();
        });

        Schema::create('tblUpdateSettings_File', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('UpdateServerUrl', 300);
            $table->boolean('AutoUpdateEnabled');
            $table->boolean('CheckOnStartup');
            $table->integer('CheckIntervalHours');
            $table->string('CurrentVersion', 32)->nullable();
            $table->dateTime('LastCheckDate')->nullable();
            $table->dateTime('CreatedDate');
            $table->dateTime('ModifiedDate')->nullable();
        });

        Schema::create('tblWindowShortcuts', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('WindowName', 255);
            $table->string('ShortcutKey', 50);
            $table->string('ActionName', 255);
            $table->string('Description', 500)->nullable();
            $table->boolean('IsActive')->nullable();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedDate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblWindowShortcuts');
        Schema::dropIfExists('tblUpdateSettings_File');
        Schema::dropIfExists('tblSystemWindows');
        Schema::dropIfExists('tblSystemUpdates');
        Schema::dropIfExists('tblSystemUpdateLogs');
        Schema::dropIfExists('tblLockProsesses');
        Schema::dropIfExists('tblDatabaseDocumentation');
        Schema::dropIfExists('tblSystemEvents');
        Schema::dropIfExists('BackupSettings');
        Schema::dropIfExists('tblhistory');
    }
};
