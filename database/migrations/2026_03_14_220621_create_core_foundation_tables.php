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
        // Stage 1: Core Foundation Tables

        Schema::create('tblBranches', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('BranchName', 255);
            $table->integer('AccountID')->nullable();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedDate')->nullable()->useCurrent();
        });

        Schema::create('tblOperations', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('OperationName', 50)->unique();
        });

        Schema::create('tblDocumentTypes', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('DocumentName', 100);
            $table->string('TableName', 50)->nullable();
            $table->string('ViewName', 50)->nullable();
            $table->string('Notes', 50)->nullable();
            $table->boolean('IsActive')->default(1);
            $table->boolean('IsHasFund')->nullable();
            $table->boolean('IsHasEntry')->nullable();
            $table->boolean('IsHasRestore')->default(0);
            $table->bigInteger('CreatedBy')->nullable();
            $table->bigInteger('BranchID')->nullable();
            $table->dateTime('CreatedDate')->nullable()->useCurrent();
        });

        Schema::create('tblAccountTypes', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('AccountType', 100)->nullable();
            $table->dateTime('CreatedDate')->nullable();
        });

        Schema::create('tblGroupTypes', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('GroupTypeName', 50);
            $table->integer('CreatedBy')->nullable();
            $table->dateTime('CreatedDate')->nullable();
            $table->integer('ModifiedBy')->nullable();
            $table->dateTime('ModifiedDate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblGroupTypes');
        Schema::dropIfExists('tblAccountTypes');
        Schema::dropIfExists('tblDocumentTypes');
        Schema::dropIfExists('tblOperations');
        Schema::dropIfExists('tblBranches');
    }
};
