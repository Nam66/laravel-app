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
        Schema::create('message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('disk', 64)->default('private');
            $table->string('path', 1024);
            $table->string('original_name');
            $table->string('mime_type', 255)->nullable();
            $table->unsignedBigInteger('size_bytes');
            $table->string('status', 32)->default('ready');
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index(['message_id', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_attachments');
    }
};
