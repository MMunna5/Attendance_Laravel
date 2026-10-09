<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('name', 100);
            $table->string('mobile', 15)->default('');
            // Comma-separated class list, same shape as the Django model
            // (Teacher::get_class_list()). A proper many-to-many pivot
            // table would be cleaner long-term -- left as-is here to keep
            // behavior 1:1 with the source app.
            $table->string('assigned_classes', 255)->default('');
            $table->enum('employment_type', ['full', 'part'])->default('full');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
