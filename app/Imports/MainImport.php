<?php

namespace App\Imports;


use App\Imports\OptionsImport;
use App\Imports\QuestionsImport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MainImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        Log::info('MainImport sheets method called.');
        return [
            'Question' => new QuestionsImport(),
            'Option' => new OptionsImport(),
        ];
    }
}

