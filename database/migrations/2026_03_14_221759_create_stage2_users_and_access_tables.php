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
        // Stage 2: Users & Access Control Tables

        Schema::create('tblUserGroups', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('GroupName', 100)->unique();
            $table->string('Description', 255)->nullable();
            $table->bigInteger('CreatedBy');
            $table->integer('BranchID')->nullable()->default(2);
            $table->dateTime('CreatedDate')->nullable()->useCurrent();
            $table->integer('GroupNumber')->default(0);
            $table->boolean('IsActive')->default(1);
            $table->integer('ModifiedBy')->nullable();
            $table->dateTime('ModifiedDate')->nullable();
        });

        Schema::create('tblUsers', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('UserName', 255);
            $table->integer('UserGroupID')->nullable()->default(0);
            $table->string('UserPassword', 255);
            $table->boolean('IsActive')->nullable()->default(1);
            $table->bigInteger('CreatedBy')->nullable();
            $table->bigInteger('BranchID')->nullable();
            $table->string('Notes', 250)->nullable();
            $table->dateTime('CreatedDate')->nullable()->useCurrent();
            $table->dateTime('LastLoginDate')->nullable();
            $table->integer('ModifiedBy')->nullable();
            $table->dateTime('ModifiedDate')->nullable();
        });

        Schema::create('tblUserGroupMapping', function (Blueprint $table) {
            $table->bigInteger('UserID');
            $table->integer('GroupID');
            $table->bigInteger('AssignedBy');
            $table->dateTime('AssignedDate')->nullable()->useCurrent();
            $table->primary(['UserID', 'GroupID']);
        });

        Schema::create('tblPermissionsAccess', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('FormCode', 100);
            $table->string('TargetType', 10);
            $table->integer('TargetID');
            $table->longText('PermissionValues');
            $table->string('DisplayText', 500)->nullable();
            $table->dateTime('CreatedDate')->useCurrent();
            $table->string('CreatedBy', 50)->nullable();
        });

        Schema::create('tblsessions', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->binary('RowVersion')->nullable(); 
            $table->bigInteger('PrevRowVersion')->nullable();
            $table->dateTime('StartTime')->nullable();
            $table->dateTime('EndTime')->nullable();
            $table->string('PCID', 200)->nullable();
            $table->string('IPAddress', 50)->nullable();
            $table->string('OSVersion', 100)->nullable();
            $table->string('MachineName', 100)->nullable();
            $table->string('OSUserName', 100)->nullable();
            $table->string('Notes', 250)->nullable();
            $table->bigInteger('UserID')->nullable();
            $table->bigInteger('BranchID')->nullable();
            $table->dateTime('EnterTime')->nullable();
            $table->boolean('IsEnded')->nullable();
            $table->string('ServiceAddress', 100)->nullable();
            $table->bigInteger('SessionID')->nullable();
            $table->boolean('ISHasToken')->nullable();
            $table->boolean('ISHasValidationCode')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblsessions');
        Schema::dropIfExists('tblPermissionsAccess');
        Schema::dropIfExists('tblUserGroupMapping');
        Schema::dropIfExists('tblUsers');
        Schema::dropIfExists('tblUserGroups');
    }
};
