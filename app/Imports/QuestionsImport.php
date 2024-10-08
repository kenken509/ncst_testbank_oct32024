<?php

namespace App\Imports;

use App\Models\Question;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionsImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Check if required keys are present
        if (!isset($row['question'])) {
            Log::warning('Row does not have a question key: ', $row);
            return null; // Skip this row if it doesn't contain a question
        }

        // Log the incoming row data for debugging
        Log::info('Processing Question Row: ', $row);

        // Use 'Attached_Image' or 'attached_image' based on your Excel header
        $imagePath = $row['attached_image'] ?? $row['Attached_Image'] ?? null;

        if ($imagePath) {
            // Ensure you have the correct path to the images
            $sourcePath = storage_path("app/public/Images/{$imagePath}");

            // Check if the source image exists before importing
            if (file_exists($sourcePath)) {
                // Define the destination path in your public storage
                $destinationPath = 'Images/' . basename($imagePath);

                // Store the image in the public disk
                Storage::disk('public')->putFileAs('Images', new File($sourcePath), basename($imagePath));
                $imagePath = $destinationPath; // Store the destination path
            } else {
                Log::warning('Image does not exist at path: ' . $sourcePath);
                $imagePath = null; // Reset if the image doesn't exist
            }
        }

        // Use the ID to find existing records or create a new one
        return Question::updateOrCreate(
            ['id' => $row['id'] ?? null], // The field to check for existing record, handle null case
            [
                'question' => $row['question'],
                'type' => $row['type'] ?? null, // Handle potential missing 'type'
                'attached_image' => $imagePath, // Store the path or null
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
