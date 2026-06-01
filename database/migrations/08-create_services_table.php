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
        Schema::create('services', function (Blueprint $table) {

            $table->uuid('id')->primary();

            $table->foreignUuid('professional_id')
                ->constrained('professional_profiles')
                ->cascadeOnDelete();

            $table->foreignUuid('company_id')
                ->nullable()
                ->constrained('companies')
                ->nullOnDelete();

            $table->string('name');

            $table->text('description')
                ->nullable();

            $table->decimal('price', 10, 2);

            $table->unsignedInteger('duration_minutes');

            $table->enum('modality', [
                'presencial',
                'remota',
                'hibrida',
            ]);

            $table->string('address')
                ->nullable();

            $table->string('link')
                ->nullable();

            $table->float('latitude')
                ->nullable();

            $table->float('longitude')
                ->nullable();

            $table->unsignedInteger('max_bookings_per_client')
                ->nullable();

            $table->unsignedInteger('min_reschedule_minutes')
                ->default(10);

            $table->unsignedInteger('buffer_minutes')
                ->default(0);

            $table->date('starts_at')
                ->nullable();

            $table->date('ends_at')
                ->nullable();

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
