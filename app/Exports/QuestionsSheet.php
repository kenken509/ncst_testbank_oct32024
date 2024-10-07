<?php

namespace App\Exports;

use App\Models\Question;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Maatwebsite\Excel\Events\AfterSheet;

class QuestionsSheet implements FromCollection, WithMapping, WithHeadings, WithTitle, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $spreadsheet; // To hold the spreadsheet instance
    protected $currentRow = 1; // Track current row for image placement

    public function collection()
    {
        $questions = Question::all();

        Log::info("Questions count: " . $questions->count()); // Log count of questions

        return $questions;
    }

    public function map($question): array
    {
        // Increase the current row count for each question
        $this->currentRow++;

        return [
            $question->id,
            $question->question,
            $question->type,
            $question->attached_image ? $question->attached_image : '', // Use empty string if there's no image   // Placeholder for the attached image (to be handled separately)$question->attached_image,
            $question->term,
            $question->subject_code_id,
            $question->author_id,
            $question->editor_id,
            $question->created_at,
            $question->updated_at,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Question',
            'Type',
            'Attached Image',
            'Term',
            'Subject Code',
            'Author ID',
            'Editor ID',
            'Created At',
            'Updated At',
        ];
    }

    public function title(): string
    {
        return 'Question'; 
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Log::info("Inside AfterSheet event");
                $sheet = $event->sheet->getDelegate();
                
                foreach ($this->collection() as $index => $question) {
                    //Log::info("Processing question ID: {$question->id}");
                    
                    // Check if the attached image field is not empty
                    if ($question->attached_image) {
                        $imagePath = storage_path("app/public/Images/{$question->attached_image}");
                        Log::info("Checking image path: {$imagePath}");
    
                        // Only attempt to access the image if the path exists
                        if (file_exists($imagePath)) {
                            Log::info("Image found for question ID: {$question->id}");
                            $drawing = new Drawing();
                            $drawing->setName('Image');
                            $drawing->setDescription('Image');
                            $drawing->setPath($imagePath);
                            $drawing->setHeight(50);
                            $drawing->setCoordinates('D' . ($index + 2));
                            $drawing->setWorksheet($sheet);
                        } else {
                            Log::warning("Image not found for question ID: {$question->id} at path: {$imagePath}");
                        }
                    } else {
                        Log::info("No image attached for question ID: {$question->id}");
                    }
                }
            },
        ];
    }

   
}
