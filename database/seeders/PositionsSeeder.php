<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Position;
use App\Models\PositionType;

class PositionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Asegurar que exista al menos un tipo de cargo
        $defaultType = PositionType::firstOrCreate(
            ['nombre' => 'General']
        );

        // 2. Obtener todos los usuarios
        $users = User::all();

        foreach ($users as $user) {
            // Verificar si ya existe una posición con este ID
            $existingPosition = Position::find($user->id);

            if (!$existingPosition) {
                // Crear la posición forzando el ID para que coincida con el usuario
                // Usamos forceCreate o asignación directa + save() si el modelo no tiene $guarded=['id']
                // Como Position tiene $fillable, id no está allí. Usaremos una instancia nueva.
                
                $position = new Position();
                $position->id = $user->id; // Forzar ID
                $position->nombre = $user->name; // Usar nombre del usuario como nombre del cargo/responsable
                $position->position_type_id = $defaultType->id;
                $position->descripcion = "Responsable generado automáticamente desde usuario: " . $user->email;
                $position->save();
            }
        }
        
        // Ajustar la secuencia del ID de la tabla positions (PostgreSQL) para evitar conflictos futuros
        // Si usamos MySQL esto no es necesario, pero en PostgreSQL sí.
        // Asumiré que es PostgreSQL por el comando anterior 'Database ... pgsql'.
        // Si fuera MySQL no pasa nada grave, pero en PGSQL el sequence se desincroniza al insertar IDs manuales.
        // Voy a intentar ejecutar el ajuste de secuencia raw.
        
        if (config('database.default') === 'pgsql') {
            $maxId = Position::max('id') ?? 1; // Asegurar al menos 1
            try {
                // Intento seguro de resetear secuencia
                DB::statement("SELECT setval(pg_get_serial_sequence('positions', 'id'), {$maxId})");
            } catch (\Exception $e) {
                // Ignorar si falla (ej: tabla no tiene secuencia serial)
            }
        }
    }
}
