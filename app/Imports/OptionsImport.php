<?php

namespace App\Imports;

use App\Models\Option;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class OptionsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        Log::info('Options processing row: ', $row);

       
               
        // return Option::updateOrCreate(
        //     ['id' => $row['id']],
        //     [
        //         'option' => $row['option'],
        //         'question_id' => $row['question_id'],
        //         'isCorrect' => $row['isCorrect'],
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]
        // );
                

         
    }
}

