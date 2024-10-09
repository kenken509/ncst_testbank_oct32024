<?php

namespace App\Http\Controllers;


use ZipArchive;
use App\Imports\MainImport;
use Illuminate\Http\Request;
use App\Imports\OptionsImport;
use App\Exports\QuestionsExport;
use App\Imports\QuestionsImport;
use App\Imports\QuestionNewImport;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class QuestionBackUpController extends Controller
{
  
    public function export()
    {
        // must delete after a successful download.>>>>>>>>>>>> ends here
        // Create the Excel file
        $excelFileName = 'questions_and_options.xlsx';
        $excelFilePath = storage_path('app/public/' . $excelFileName);
        
        // Store the Excel file
        //Excel::store(new QuestionsExport(), $excelFileName);
        
        try {
            
            Excel::store(new QuestionsExport(), $excelFileName, 'public');
            
            // Log the creation of the Excel file
            Log::info('Excel file created at: ' . storage_path('app/public/' . $excelFileName));
        } catch (\Exception $e) {
            // Log any error that occurs during file creation
            Log::error('Error creating Excel file: ' . $e->getMessage());
            return response()->json(['error' => 'Could not create Excel file'], 500);
        }

        // Create the zip file
        $zip = new ZipArchive();
        $zipFileName = 'images.zip';
        $zipFilePath = storage_path('app/public/' . $zipFileName);

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            // Add images to the zip
            $files = Storage::files('public/Images');

            foreach ($files as $file) {
                // Adding each image file to the zip
                $zip->addFile(storage_path('app/' . $file), basename($file));
            }

            // Close the zip file
            $zip->close();
        } else {
            return response()->json(['error' => 'Could not create zip file'], 500);
        }

        // Return a response with links to download both files
        return response()->json([
            'excel_download_url' => url("storage/{$excelFileName}"),
            'zip_download_url' => url("storage/{$zipFileName}"),
        ]);
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
        
        return redirect()->route('questions.show')->with('success', 'Data imported successfully!');
    }
}
