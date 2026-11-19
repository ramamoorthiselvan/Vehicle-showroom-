<?php

namespace Database\Seeders;

use App\Models\ModelShowroom;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ModelShowroomSeeder extends Seeder
{
    public function run(): void
    {
        $models = [
            ['model_name' => 'Q50',  'year' => 2014, 'price' => 37500, 'file' => 'q50.png',  'status' => true,  'display_order' => 1],
            ['model_name' => 'QX70', 'year' => 2014, 'price' => 53350, 'file' => 'qx70.png', 'status' => true,  'display_order' => 2],
            ['model_name' => 'QX50', 'year' => 2014, 'price' => 37900, 'file' => 'qx50.png', 'status' => true,  'display_order' => 3],
            ['model_name' => 'QX60', 'year' => 2014, 'price' => 43000, 'file' => 'qx60.png', 'status' => true,  'display_order' => 4],
            ['model_name' => 'Q50 Hybrid', 'year' => 2014, 'price' => 44900, 'file' => 'q50.png',  'status' => true,  'display_order' => 5],
            ['model_name' => 'QX80', 'year' => 2014, 'price' => 68900, 'file' => 'qx60.png', 'status' => false, 'display_order' => 6],
        ];

        Storage::disk('public')->makeDirectory('models');

        foreach ($models as $row) {
            $file = $row['file'];
            unset($row['file']);

            $target = 'models/seed-'.strtolower(str_replace(' ', '-', $row['model_name'])).'.png';
            Storage::disk('public')->put($target, File::get(public_path('assets/images/sample-vehicles/'.$file)));

            ModelShowroom::updateOrCreate(
                ['model_name' => $row['model_name'], 'year' => $row['year']],
                $row + ['image' => $target]
            );
        }
    }
}
