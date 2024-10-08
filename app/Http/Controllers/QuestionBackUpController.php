<?php

namespace App\Http\Controllers;


use App\Imports\MainImport;
use Illuminate\Http\Request;
use App\Imports\OptionsImport;
use App\Exports\QuestionsExport;
use App\Imports\QuestionsImport;
use App\Imports\QuestionNewImport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class QuestionBackUpController extends Controller
{
    public function export()
    {
        return Excel::download(new QuestionsExport(), 'questions_and_options.xlsx'); // Trigger the download
    }

    public function import(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        // Store the uploaded file temporarily
        $filePath = $request->file('file')->store('temp.xlsx');
        Log::info('Stored file: ', ['file' => $filePath]);

        // Import from both sheets
        try {
            Excel::import(new MainImport(), storage_path("app/{$filePath}"));
            Log::info('Import successful >>>>>>>>>>>>>>>>>>>>>>>');
        } catch (\Exception $e) {
            Log::error('Import failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Data import failed: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Data imported successfully!');
    }
}
