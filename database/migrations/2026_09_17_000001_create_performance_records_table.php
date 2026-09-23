<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_records', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->string('name');
            $table->string('email')->index();
            $table->string('status', 20)->index();
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->index(['status', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_records');
    }
};