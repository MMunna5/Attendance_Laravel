<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('roll_no', 20);
            $table->string('name', 150);
            $table->string('class_name', 20);
            $table->string('section', 10)->default('');
            $table->string('parent_mobile', 15)->default('');
            $table->timestamps();

            $table->unique(['class_name', 'section', 'roll_no'], 'uniq_class_section_roll');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
