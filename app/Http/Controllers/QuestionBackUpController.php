<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\QuestionsExport;
use Maatwebsite\Excel\Facades\Excel;

class QuestionBackUpController extends Controller
{
    public function export()
    {
        return Excel::download(new QuestionsExport(), 'questions_and_options.xlsx'); // Trigger the download
    }
}
