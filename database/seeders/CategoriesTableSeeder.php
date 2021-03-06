<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categories')->insert([
            ['name' => 'Мобильные телефоны', 'code' => 'moniles', 'description' => 'Описание мобилок'],
            ['name' => 'Портативная техника', 'code' => 'protable', 'description' => 'Описание портативки'],
            ['name' => 'Бытовая техника', 'code' => 'technics', 'description' => 'Описание бытовухи'],
        ]);
    }
}
