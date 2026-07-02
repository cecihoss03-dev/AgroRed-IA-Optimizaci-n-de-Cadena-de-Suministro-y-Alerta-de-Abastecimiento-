<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // IMPORTANTE PARA ACTIVAR POSTGIS

class CreateAgroRedSystemTables extends Migration 
{
    public function up(): void {
        // ACTIVA POSTGIS DIRECTAMENTE EN TU BASE DE DATOS
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis;');

        // 1. Usuarios
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['productor', 'mayorista', 'admin'])->default('mayorista');
            $table->timestamps();
        });

        // 2. Centros de Producción
        Schema::create('production_centers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('producer_id')->constrained('users')->cascadeOnDelete();
            $table->string('municipality');
            $table->geometry('location', subtype: 'point', srid: 4326);
            $table->timestamps();
            $table->spatialIndex('location');
        });

        // 3. Puntos de Mercado
        Schema::create('market_points', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['mayorista', 'minorista']);
            $table->geometry('location', subtype: 'point', srid: 4326);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->spatialIndex('location');
        });

        // 4. Rutas de Acceso
        Schema::create('access_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->geometry('path', subtype: 'multilinestring', srid: 4326);
            $table->enum('priority', ['critica', 'alternativa'])->default('alternativa');
            $table->timestamps();
            $table->spatialIndex('path');
        });

        // 5. Bloqueos de Carreteras
        Schema::create('road_blockages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('access_route_id')->constrained()->cascadeOnDelete();
            $table->string('segment_name');
            $table->enum('cause', ['bloqueo_social', 'derrumbe', 'clima']);
            $table->enum('severity', ['total', 'parcial']);
            $table->timestamp('reported_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['resolved_at', 'severity']);
        });

        // 6. Productos
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->string('unit_of_measure');
            $table->timestamps();
        });

        // 7. Oferta Actual
        Schema::create('product_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('production_center_id')->constrained()->cascadeOnDelete();
            $table->decimal('available_stock', 10, 2);
            $table->decimal('current_price', 10, 2);
            $table->timestamps();
        });

        // 8. Historial de Precios
        Schema::create('price_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('market_point_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->date('recorded_date');
            $table->boolean('had_active_blockage')->default(false);
            $table->timestamps();
            $table->index(['product_id', 'market_point_id', 'recorded_date']);
        });

        // 9. Predicciones de la IA
        Schema::create('price_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('market_point_id')->constrained()->cascadeOnDelete();
            $table->decimal('predicted_price', 10, 2);
            $table->decimal('confidence_score', 4, 3);
            $table->json('scenario_input');
            $table->json('model_explanation')->nullable();
            $table->date('valid_for_date');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('price_predictions');
        Schema::dropIfExists('price_history');
        Schema::dropIfExists('product_listings');
        Schema::dropIfExists('products');
        Schema::dropIfExists('road_blockages');
        Schema::dropIfExists('access_routes');
        Schema::dropIfExists('market_points');
        Schema::dropIfExists('production_centers');
        Schema::dropIfExists('users');
    }
}