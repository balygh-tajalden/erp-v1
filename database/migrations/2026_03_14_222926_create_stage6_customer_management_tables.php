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
        // Stage 6: Customer Management & Sales Tables

        Schema::create('tblCustomers', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('CustomerName', 255);
            $table->string('CustomerAddress', 500)->nullable();
            $table->string('Phone1', 20)->nullable();
            $table->string('Phone2', 20)->nullable();
            $table->string('Email', 100)->nullable();
            $table->bigInteger('AccountID')->nullable();
            $table->boolean('IsActive')->default(1);
            $table->bigInteger('CreatedBy')->nullable();
            $table->bigInteger('BranchID')->nullable();
            $table->dateTime('CreatedDate')->useCurrent();
            $table->string('Notes', 500)->nullable();
            $table->decimal('Latitude', 10, 8)->nullable();
            $table->decimal('Longitude', 11, 8)->nullable();
        });

        Schema::create('tblPackageType', function (Blueprint $table) {
            $table->increments('ID');
            $table->string('TypeName', 100);
            $table->string('Notes', 250)->nullable();
        });

        Schema::create('tblPackages', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('PackageName', 255);
            $table->integer('TypeID');
            $table->decimal('BasePrice', 18, 2);
            $table->integer('DurationDays')->nullable();
            $table->boolean('IsActive')->default(1);
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedDate')->useCurrent();
        });

        Schema::create('tblPackagePrices', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->unsignedBigInteger('PackageID');
            $table->decimal('Price', 18, 2);
            $table->dateTime('EffectiveDate');
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedDate')->useCurrent();
            
            $table->foreign('PackageID')->references('ID')->on('tblPackages')->onDelete('cascade');
        });

        Schema::create('tblCustomerSubscriptions', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->unsignedBigInteger('CustomerID');
            $table->unsignedBigInteger('PackageID');
            $table->dateTime('StartDate');
            $table->dateTime('EndDate')->nullable();
            $table->decimal('PriceAtSubscription', 18, 2);
            $table->boolean('IsActive')->default(1);
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedDate')->useCurrent();

            $table->foreign('CustomerID')->references('ID')->on('tblCustomers')->onDelete('cascade');
            $table->foreign('PackageID')->references('ID')->on('tblPackages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tblCustomerSubscriptions');
        Schema::dropIfExists('tblPackagePrices');
        Schema::dropIfExists('tblPackages');
        Schema::dropIfExists('tblPackageType');
        Schema::dropIfExists('tblCustomers');
    }
};
