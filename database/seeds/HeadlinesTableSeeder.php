<?php

use App\Http\Controllers\Templates\TemplateDefault;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HeadlinesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('headlines')->insert(TemplateDefault::HEADLINES_DEFAULT);
    }
}
