<?php

use App\Models\Booking;
use App\Models\Doctor;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('id');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->uuid('slug')->nullable()->unique()->after('id');
        });

        Doctor::withoutEvents(function () {
            Doctor::withTrashed()->whereNull('slug')->eachById(function (Doctor $doctor) {
                $doctor->forceFill(['slug' => Str::slug($doctor->code).'-'.$doctor->id])->save();
            });
        });

        Booking::withoutEvents(function () {
            Booking::query()->whereNull('slug')->eachById(function (Booking $booking) {
                $booking->forceFill(['slug' => (string) Str::uuid()])->save();
            });
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
