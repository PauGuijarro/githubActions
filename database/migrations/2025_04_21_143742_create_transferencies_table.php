<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('transferencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('origen_id')->constrained('comptes');
            $table->foreignId('desti_id')->constrained('comptes');
            $table->decimal('quantitat', 10, 2);
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transferencies');
    }
};
