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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('notif_eventos_otros_pueblos')->default(true)->after('tema');
            $table->boolean('notif_eventos_mi_pueblo')->default(true)->after('notif_eventos_otros_pueblos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['notif_eventos_otros_pueblos', 'notif_eventos_mi_pueblo']);
        });
    }
};
