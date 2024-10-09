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

        // Get the image data from the row
        $imageData = $row['attached_image'] ?? null; // This should contain the actual image

        $imagePath = null; // Initialize the image path

        if ($imageData) {
            // Assuming the image data contains the file name at the beginning, like "filename:base64data"

            Log::info('Raw Image Data: ', ['data' => $imageData]);
            if (strpos($imageData, ':') !== false) {
                list($imageFileName, $base64Data) = explode(':', $imageData, 2);
                $imageFileName = trim($imageFileName); // Use the provided file name
                $base64Data = trim($base64Data); // Get the base64 data

                // Decode the image data
                $imageData = base64_decode($base64Data);
                if ($imageData === false) {
                    Log::warning('Base64 decode failed for image data in row: ', $row);
                    return null; // Skip this row if decoding fails
                }

                // Define the destination path in your public storage
                $destinationPath = 'Images/' . basename($imageFileName); // Use the provided file name

                // Store the image in the public disk
                Storage::disk('public')->put($destinationPath, $imageData);

                // Set the path to be stored in the database
                $imagePath = $destinationPath;
            } else {
                Log::warning('Image data format is not valid in row: ', $row);
            }
        } else {
            Log::warning('No image data found in the row.');
        }

        // Continue with the rest of the logic for updating or creating a question
        return Question::updateOrCreate(
            ['id' => $row['id'] ?? null], // The field to check for existing record, handle null case
            [
                'question' => $row['question'],
                'type' => $row['type'] ?? null, // Handle potential missing 'type'
                'attached_image' => $imagePath, // Store the path
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
