<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tecnicos', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->unique()
                ->constrained('users')
                ->nullOnDelete();
        });

        DB::table('tecnicos')
            ->whereNull('user_id')
            ->orderBy('id')
            ->get()
            ->each(function ($tecnico): void {
                $user = DB::table('users')->where('email', $tecnico->email)->first();

                $userId = $user?->id;

                if (!$userId) {
                    $userId = DB::table('users')->insertGetId([
                        'name' => $tecnico->nome,
                        'email' => $tecnico->email,
                        'password' => $tecnico->password,
                        'role' => 'usuario',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $alreadyLinked = DB::table('tecnicos')
                    ->where('user_id', $userId)
                    ->exists();

                if (!$alreadyLinked) {
                    DB::table('tecnicos')
                        ->where('id', $tecnico->id)
                        ->update([
                            'user_id' => $userId,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('tecnicos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
