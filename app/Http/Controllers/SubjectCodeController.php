<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Department;
use App\Models\SubjectCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ExcelSubjectCodeImport;
use Illuminate\Support\Facades\Storage;

class SubjectCodeController extends Controller
{
    public function showSubjectCodes()
    {
        $codes = SubjectCode::with('department','division')->latest()->get();
        //dd($codes);
        return inertia('Dashboard/SubjectCodes/SubjectCodesAll',[
            'codes' => $codes,
        ]);
    }

    public function showAddSubjectCode()
    {
        
        $departments = Department::with('divisions')->latest()->get();
        
        return inertia('Dashboard/SubjectCodes/SubjectCodeAdd',[
            'departments' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        
        $request->validate([
            'name' => 'required|unique:subject_codes',
        ],[
            'name' => 'Existing code detected, please try different code.'
        ]);

        try 
        {
            DB::beginTransaction();
            $newSubjectCode = new SubjectCode();

            $newSubjectCode->name = $request->name;
            $newSubjectCode->description = $request->description;
            $newSubjectCode->department_id = $request->department_id;
            $newSubjectCode->division_id = $request->division_id;
            $newSubjectCode->save();
            
            DB::commit();
            return redirect()->route('subject.codes.show')->with('success', 'Successfully added new subject code.');
        }catch(\Exception $e)
        {
            DB::rollback();
            Log::error('error saving new code: '.$e.getMessag());

            return redirect()->back()->with('error', 'Failed to save new code');
        }
       

    }

    public function updateShow($id)
    {
        $codeToUpdate = SubjectCode::with(['department', 'division'])->findOrFail($id);
        $departments = Department::with('divisions')->latest()->get();
        
        return inertia('Dashboard/SubjectCodes/SubjectCodeUpdate', [
            'codeToUpdate' => $codeToUpdate,
            'departments'   => $departments, 
        ]);
    }   
    

    public function update(Request $request)
    {
        
        try{
            DB::beginTransaction();

            $codeToUpdate = SubjectCode::findOrFail($request->id);

            $codeToUpdate->name = $request->name;
            $codeToUpdate->description = $request->description;
            $codeToUpdate->department_id = $request->department_id;
            $codeToUpdate->division_id = $request->division_id;
            $codeToUpdate->save();
            


            DB::commit();
            return redirect()->route('subject.codes.show')->with('success', 'Successfully updated subject code');
        }catch(Exception $e){
            DB::rollback();
            
            Log::error('error updating code: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to update subject code.');
        }
        
    }

    public function destroy($id)
    {
        
        try{

            DB::beginTransaction();
            $subjectCode = SubjectCode::findOrFail($id);

            $subjectCode->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Successfully deleted subject code.');
        }
        catch(\Exception $e)
        {
            DB::rollback();

            Log::error('Error deleting subject code: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete subject code.');
        }
        
    }

    public function import(Request $request)
    {
       
        try
        {
            DB::beginTransaction();
            $department_id = $request->department_id;
            $division_id = $request->division_id;

            // Pass the additional variable to the import class
            Excel::import(new ExcelSubjectCodeImport($department_id, $division_id), $request->file('file'));

            DB::commit();

            return redirect()->route('subject.codes.show')->with('success','Successfully imported subject codes.');
        }
        catch (\Exception $e)
        {
            DB::rollback();

            Log::info('Error importing subject codes: '.$e);

            return redirect()->back()->with('error', 'Excel Import Failed!');
        }
        
    }

    public function downloadExcelFormat()
    {
        
        $filePath = '/excel_format/subject_code_import_format.xlsx';

        if (Storage::disk('public')->exists($filePath)) {
            Log::info('download excel format successfully!!!');
            return Storage::disk('public')->download($filePath);
        }
        else
        {
            Log::info('download excel format not exist!!!');
        }

        
        return abort(404); // Return 404 if the file doesn't exist
    }
}
