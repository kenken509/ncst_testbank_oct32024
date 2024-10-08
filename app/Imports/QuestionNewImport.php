<?php

namespace App\Imports;

use App\Models\Question;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionNewImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        Log::info('question row info: '.$row);
        return Question::updateOrCreate(
            ['id' => $row['id'] ?? null], // The field to check for existing record, handle null case
            [
                'question' => $row['question'],
                'type' => $row['type'] ?? null, // Handle potential missing 'type'
                'attached_image' => $row['attached_image'] ?? null, // Store the path or null
                'term' => $row['term'] ?? null, // Handle potential missing 'term'
                'subject_code_id' => $row['subject_code'] ?? null, // Handle potential missing 'subject_code'
                'author_id' => $row['author_id'] ?? null, // Handle potential missing 'author_id'
                'editor_id' => $row['editor_id'] ?? null, // Handle potential missing 'editor_id'
                'created_at' => \Carbon\Carbon::parse($row['created_at'] ?? now()), // Use now() if not set
                'updated_at' => now(), // Use now() for updated_at
            ]
        );
    }
}
