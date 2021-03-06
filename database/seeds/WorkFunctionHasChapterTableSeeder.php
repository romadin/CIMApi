<?php

use App\Http\Controllers\Templates\TemplateDefault;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WorkFunctionHasChapterTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('work_function_has_chapter')->insert(TemplateDefault::WORK_FUNCTION_HAS_CHAPTER);
    }
}
