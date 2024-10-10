<?php

namespace App\Http\Controllers;


use ZipArchive;
use App\Imports\MainImport;
use Illuminate\Http\Request;
use App\Imports\OptionsImport;
use App\Jobs\DeleteBackupFiles;
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
        $randomNumber = rand(1,9999);
        $randomString = str()->random(5);
        

        $date = date('m_d_Y');
        $excelFileName = 'questions_options_backup-'.$date.'_'.$randomString.$randomNumber.'.xlsx';
        $excelFilePath = storage_path('app/public/' . $excelFileName);
        
       
        
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
        $zipFileName = 'images_backup-'.$date.'_'.$randomString.$randomNumber.'.zip';
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

        
        $response =  response()->json([
            'excel_download_url' => url("storage/{$excelFileName}"),
            'zip_download_url' => url("storage/{$zipFileName}"),
        ]);

        // Dispatch the delete job with a delay
        $delay = now()->addMinutes(10); // this will be executed after 10mins 
        DeleteBackupFiles::dispatch([$excelFileName, $zipFileName])->delay($delay);

        return $response;
    }

    public function import(Request $request)
    {
        // Validate the uploaded files
        $request->validate([
            'excelFile' => 'required|mimes:xlsx,xls',
            'imageZipFile' => 'required|mimes:zip',
        ]);

        

       // Store the uploaded Excel file temporarily
        $excelFilePath = $request->file('excelFile')->store('temp.xlsx');
        Log::info('Stored Excel file: ', ['file' => $excelFilePath]);

        // Extract images from the zip file
        $zipFilePath = $request->file('imageZipFile')->store('temp.zip');
        Log::info('Stored Zip file: ', ['file' => $zipFilePath]);
        // Import from both sheets

        try {
            // Extract the zip file
            $zip = new \ZipArchive;
            if ($zip->open(storage_path("app/{$zipFilePath}")) === TRUE) {
                $extractPath = storage_path('app/public/Images');
                $zip->extractTo($extractPath);
                $zip->close();
                Log::info('Images extracted successfully.');
            } else {
                Log::error('Failed to open zip file.');
                return redirect()->back()->with('error', 'Failed to open the zip file.');
            }
    
            // Import from the Excel sheet
            Excel::import(new MainImport(), storage_path("app/{$excelFilePath}"));
            Log::info('Import successful >>>>>>>>>>>>>>>>>>>>>>>');

            // Delete temporary files after successful import and extraction
            Storage::delete($excelFilePath);
            Storage::delete($zipFilePath);
            Log::info('Temporary files deleted successfully.');

            
        } catch (\Exception $e) {
            Log::error('Import failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Data import failed: ' . $e->getMessage());
        }
        
        return redirect()->route('questions.show')->with('success', 'Data imported successfully!');
    }
}
