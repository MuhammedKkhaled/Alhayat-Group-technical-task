<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->restrictOnDelete();
            $table->string('code');
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedSmallInteger('duration_minutes');
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['doctor_id', 'code']);
        });

        Schema::create('doctor_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week')->nullable();
            $table->date('date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->boolean('is_available')->default(false);
            $table->timestamps();
            $table->unique(['doctor_id', 'day_of_week']);
            $table->unique(['doctor_id', 'date']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE doctor_schedules ADD CONSTRAINT doctor_schedules_kind_check CHECK ((day_of_week IS NOT NULL AND date IS NULL) OR (day_of_week IS NULL AND date IS NOT NULL))");
            DB::statement("ALTER TABLE doctor_schedules ADD CONSTRAINT doctor_schedules_hours_check CHECK ((day_of_week IS NULL AND is_available = 0 AND start_time IS NULL AND end_time IS NULL) OR (day_of_week IS NOT NULL AND ((is_available = 0 AND start_time IS NULL AND end_time IS NULL) OR (is_available = 1 AND start_time < end_time))))");
            DB::statement('ALTER TABLE doctor_schedules ADD CONSTRAINT doctor_schedules_weekday_check CHECK (day_of_week IS NULL OR day_of_week BETWEEN 0 AND 6)');
            DB::statement('ALTER TABLE services ADD CONSTRAINT services_price_check CHECK (price >= 0)');
            DB::statement('ALTER TABLE services ADD CONSTRAINT services_duration_check CHECK (duration_minutes > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_schedules');
        Schema::dropIfExists('services');
        Schema::dropIfExists('doctors');
    }
};
