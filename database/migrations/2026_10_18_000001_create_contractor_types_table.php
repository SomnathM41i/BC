<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contractor_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $types = [
            'Centring',
            'Masonry',
            'General',
            'Civil/RCC',
            'Painting',
            'Flooring',
            'Plumbing',
            'Electrical',
            'Waterproofing',
            'Fabrication',
            'Carpentry',
            'Excavation',
            'Other',
        ];

        $now = now();
        foreach ($types as $name) {
            DB::table('contractor_types')->insert([
                'name' => $name,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contractor_types');
    }
};
