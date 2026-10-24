<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('doctor_id')->constrained()->restrictOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->decimal('total_price', 10, 2);
            $table->unsignedSmallInteger('total_duration_minutes');
            $table->string('status')->default('confirmed');
            $table->string('doctor_name_snapshot');
            $table->dateTime('rescheduled_at')->nullable();
            $table->timestamps();
            $table->index(['doctor_id', 'starts_at', 'ends_at']);
            $table->index(['doctor_id', 'status']);
        });

        Schema::create('booking_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete();
            $table->string('service_name_snapshot');
            $table->decimal('price_snapshot', 10, 2);
            $table->unsignedSmallInteger('duration_minutes_snapshot');
            $table->timestamps();
            $table->unique(['booking_id', 'service_id']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE bookings ADD CONSTRAINT bookings_interval_check CHECK (starts_at < ends_at)');
            DB::statement('ALTER TABLE bookings ADD CONSTRAINT bookings_price_check CHECK (total_price >= 0)');
            DB::statement('ALTER TABLE bookings ADD CONSTRAINT bookings_duration_check CHECK (total_duration_minutes > 0)');
            DB::statement('ALTER TABLE booking_service ADD CONSTRAINT booking_service_price_check CHECK (price_snapshot >= 0)');
            DB::statement('ALTER TABLE booking_service ADD CONSTRAINT booking_service_duration_check CHECK (duration_minutes_snapshot > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_service');
        Schema::dropIfExists('bookings');
    }
};
