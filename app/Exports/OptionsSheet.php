<?php

namespace App\Exports;

use App\Models\Option;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Illuminate\Support\Facades\Log;


class OptionsSheet implements FromCollection, WithMapping, WithHeadings, WithTitle, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $spreadsheet; // To hold the spreadsheet instance
    protected $currentRow = 1; // Track current row for image placement

    public function collection()
    {
        return Option::all(); // Fetch all options
    }

    public function map($option): array
    {
         // Increase the current row count for each option
         $this->currentRow++;

        return [
            $option->id,
            $option->option, // Assuming this is the foreign key
            $option->isCorrect, // The option text
            $option->question_id,
            $option->created_at,
            $option->updated_at,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Option',
            'isCorrect',
            'question_id',
            'created_at',
            'updated_at',
        ];
    }
    
    public function title(): string
    {
        return 'Option';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                Log::info("Inside AfterSheet event for Options");
                $sheet = $event->sheet->getDelegate();

                foreach ($this->collection() as $index => $option) {
                    Log::info("Processing option ID: {$option->id}");

                    // Check if the option has an associated image
                    if ($option->option) { // Assuming you have an `attached_image` field
                        $imagePath = storage_path("app/public/Images/{$option->option}");
                        Log::info("Checking image path: {$imagePath}");

                        // Only attempt to access the image if the path exists
                        if (file_exists($imagePath)) {
                            Log::info("Image found for option ID: {$option->id}");
                            $drawing = new Drawing();
                            $drawing->setName('Image');
                            $drawing->setDescription('Image');
                            $drawing->setPath($imagePath);
                            $drawing->setHeight(50); // Set height for the image
                            $drawing->setCoordinates('B' . ($index + 2)); // Adjust coordinates as necessary
                            $drawing->setWorksheet($sheet);
                        } else {
                            Log::warning("Image not found for option ID: {$option->id} at path: {$imagePath}");
                        }
                    } else {
                        Log::info("No image attached for option ID: {$option->id}");
                    }
                }
            },
        ];
    }
}
