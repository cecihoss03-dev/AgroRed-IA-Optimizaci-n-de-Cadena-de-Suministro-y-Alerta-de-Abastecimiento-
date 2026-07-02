<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AgroRedSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. USUARIOS (Productores y Administradores)
        // ==========================================
        
        // Ciframos una contraseña genérica para las pruebas del MVP
        $password = Hash::make('agrored2026');

        DB::table('users')->updateOrInsert(
            ['email' => 'asencio@agro.com'],
            [
                'name' => 'Don Asencio Choque',
                'password' => $password,
                'role' => 'productor',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $productorId = DB::table('users')->where('email', 'asencio@agro.com')->value('id');

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@agrored.com'],
            [
                'name' => 'Ceci Hoss Admin',
                'password' => $password,
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $adminId = DB::table('users')->where('email', 'admin@agrored.com')->value('id');

        DB::table('users')->updateOrInsert(
            ['email' => 'comerciante@agro.com'],
            [
                'name' => 'Comerciante AgroRed',
                'password' => $password,
                'role' => 'mayorista',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        
        $productorId = DB::table('users')->insertGetId([
            'name' => 'Asencio',
            'email' => 'asencio@agro.com',
            'password' => $password,
            'role' => 'productor',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $adminId = DB::table('users')->insertGetId([
            'name' => 'Admin',
            'email' => 'admin@agrored.com',
            'password' => $password,
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        // ==========================================
        // 2. PRODUCTOS (Catálogo Maestro Chuquisaqueño)
        // ==========================================
        
        $productos = [
            ['name' => 'Papa Imilla', 'category' => 'Tubérculos', 'unit_of_measure' => 'Arroba'],
            ['name' => 'Tomate Perita', 'category' => 'Verduras', 'unit_of_measure' => 'Arroba'],
            ['name' => 'Cebolla Roja', 'category' => 'Verduras', 'unit_of_measure' => 'Arroba'],
            ['name' => 'Zanahoria', 'category' => 'Verduras', 'unit_of_measure' => 'Arroba'],
            ['name' => 'Maní Colorado', 'category' => 'Legumbres', 'unit_of_measure' => 'Quintal'],
            ['name' => 'Choclo Local', 'category' => 'Cereales', 'unit_of_measure' => '100 Unidades'],
        ];

        $productIds = [];
        foreach ($productos as $prod) {
            $productIds[$prod['name']] = DB::table('products')->insertGetId(array_merge($prod, [
                'created_at' => now(), 'updated_at' => now()
            ]));
        }


        // ==========================================
        // 3. CENTROS DE PRODUCCIÓN (Puntos GPS Reales)
        // ==========================================
        
        // Usamos ST_GeomFromText('POINT(Longitud Latitud)', 4326) para georreferenciación nativa en Postgres
        $centroTarabuco = DB::table('production_centers')->insertGetId([
            'name' => 'Asociación de Productores Jatun Yampara',
            'producer_id' => $productorId,
            'municipality' => 'Tarabuco',
            'location' => DB::raw("ST_GeomFromText('POINT(-64.9152 -19.1672)', 4326)"), // Coordenadas Tarabuco
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $centroZudanez = DB::table('production_centers')->insertGetId([
            'name' => 'Huertas Comunitarias Zudáñez Centro',
            'producer_id' => $productorId,
            'municipality' => 'Zudáñez',
            'location' => DB::raw("ST_GeomFromText('POINT(-64.7001 -19.1235)', 4326)"), // Coordenadas Zudáñez
            'created_at' => now(), 'updated_at' => now(),
        ]);


        // ==========================================
        // 4. PUNTOS DE MERCADO EN SUCRE (Destinos)
        // ==========================================
        
        $mercadoMorro = DB::table('market_points')->insertGetId([
            'name' => 'Mercado Mayorista El Morro',
            'type' => 'mayorista',
            'location' => DB::raw("ST_GeomFromText('POINT(-65.2442 -19.0494)', 4326)"), // Zona Barrio Lindo / El Morro
            'active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $mercadoCentral = DB::table('market_points')->insertGetId([
            'name' => 'Mercado Central Sucre',
            'type' => 'minorista',
            'location' => DB::raw("ST_GeomFromText('POINT(-65.2594 -19.0481)', 4326)"), // Centro Histórico
            'active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);


        // ==========================================
        // 5. RUTAS DE ACCESO (Carreteras Reales ABC)
        // ==========================================
        
        // Usamos MULTILINESTRING para dibujar la trayectoria simplificada entre ciudades conectadas
        DB::table('access_routes')->insert([
            'name' => 'Ruta Diagonal Jaime Mendoza (Sucre - Tarabuco - Zudáñez)',
            // Conecta Sucre -> Yamparáez -> Tarabuco -> Zudáñez
            'path' => DB::raw("ST_GeomFromText('MULTILINESTRING((-65.2442 -19.0494, -65.1325 -19.1021, -64.9152 -19.1672, -64.7001 -19.1235))', 4326)"),
            'priority' => 'critica',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        DB::table('access_routes')->insert([
            'name' => 'Ruta F7 (Sucre - Puente Arce - Cochabamba)',
            // Conecta Sucre -> Aiquile -> Cochabamba
            'path' => DB::raw("ST_GeomFromText('MULTILINESTRING((-65.2442 -19.0494, -65.1412 -18.8231, -65.2015 -18.3312))', 4326)"),
            'priority' => 'critica',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $rutaPotosiId = DB::table('access_routes')->insertGetId([
            'name' => 'Ruta F5 (Sucre - Yotala - Potosí)',
            // Conecta Sucre -> Yotala -> Puente Méndez -> Potosí
            'path' => DB::raw("ST_GeomFromText('MULTILINESTRING((-65.2594 -19.0481, -65.2662 -19.1511, -65.5121 -19.4120))', 4326)"),
            'priority' => 'critica',
            'created_at' => now(), 'updated_at' => now(),
        ]);


        // ==========================================
        // 6. BLOQUEOS DE CARRETERAS (Datos Similares a la Realidad)
        // ==========================================
        
        // Simulamos un bloqueo activo en la salida hacia Potosí (Yotala) para testear la IA espacial
        DB::table('road_blockages')->insert([
            'access_route_id' => $rutaPotosiId,
            'segment_name' => 'Tranca de Yotala - Km 15',
            'cause' => 'bloqueo_social',
            'severity' => 'total',
            'reported_at' => now()->subHours(5), // Hace 5 horas
            'resolved_at' => null, // Sigue activo e impactando la logística
            'created_at' => now(), 'updated_at' => now(),
        ]);


        // ==========================================
        // 7. STOCK ACTUAL (Oferta Real en Finca)
        // ==========================================
        
        // Don Asencio tiene papas listas en Tarabuco
        DB::table('product_listings')->insert([
            'product_id' => $productIds['Papa Imilla'],
            'production_center_id' => $centroTarabuco,
            'available_stock' => 150.00, // 150 arrobas
            'current_price' => 45.00,    // 45 Bs la arroba en origen
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // Y tomates listos en Zudáñez
        DB::table('product_listings')->insert([
            'product_id' => $productIds['Tomate Perita'],
            'production_center_id' => $centroZudanez,
            'available_stock' => 80.00,  // 80 arrobas
            'current_price' => 60.00,   // 60 Bs la arroba en origen
            'created_at' => now(), 'updated_at' => now(),
        ]);


        // ==========================================
        // 8. HISTORIAL DE PRECIOS (Para alimentar la IA)
        // ==========================================
        
        // Registramos precios históricos de los últimos 2 días para la Papa en El Morro
        DB::table('price_history')->insert([
            [
                'product_id' => $productIds['Papa Imilla'],
                'market_point_id' => $mercadoMorro,
                'price' => 50.00, // Precio normal sin conflictos
                'recorded_date' => now()->subDays(2)->format('Y-m-d'),
                'had_active_blockage' => false,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'product_id' => $productIds['Papa Imilla'],
                'market_point_id' => $mercadoMorro,
                'price' => 65.00, // El precio subió porque empezó un bloqueo en las rutas
                'recorded_date' => now()->subDay()->format('Y-m-d'),
                'had_active_blockage' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]
        ]);
    }
}
