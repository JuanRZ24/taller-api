<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            'Desarrollo web',
            'Desarrollo móvil',
            'Bases de datos',
            'Ciencia de datos',
            'Diseño',
            'Redes y seguridad',
            'Negocios y marketing',
            'Idiomas',
        ];

        foreach ($categorias as $nombre) {
            Category::firstOrCreate(['name' => $nombre]);
        }
    }
}