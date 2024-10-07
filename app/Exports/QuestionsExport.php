<?php

namespace App\Exports;

//use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class QuestionsExport implements  WithMultipleSheets
{
    // /**
    // * @return \Illuminate\Support\Collection
    // */
    // public function collection()
    // {
    //     //
    // }

    public function sheets(): array
    {
        return [
            new QuestionsSheet(), // First sheet for questions
            new OptionsSheet(),   // Second sheet for options
        ];
    }
}
