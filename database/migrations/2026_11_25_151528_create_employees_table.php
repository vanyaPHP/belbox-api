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
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('head_id')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();
            $table->foreignUuid('manager_id')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();

            $table->string('status');
            $table->date('start_date');
            $table->string('phone_number')->nullable()->unique();
            $table->string('personal_email')->nullable()->unique();
            $table->date('birth_date');
            $table->string('address');
            $table->string('iban', 28)->nullable()->unique();

            $table->jsonb('metadata');
            $table->date('end_date')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
