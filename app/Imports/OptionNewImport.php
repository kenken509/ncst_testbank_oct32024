<?php

namespace App\Imports;

use App\Models\Option;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OptionNewImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        Log::info('Processing Options Row: ', $row);
        return Question::updateOrCreate(
            ['id' => $row['id'] ?? null], // The field to check for existing record, handle null case

            [
                'option' => $row['option'],
                'question_id' => $row['question_id'],
                'isCorrect' => $row['isCorrect'],
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
