<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Imports\ExcelDivisionImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class DivisionController extends Controller
{
    public function showDivision()
    {
        return inertia('Dashboard/Division/DivisionAll', [
            'division' => Division::with('department', 'faculties')->latest()->get(),
        ]);
    }

    public function destroy($id)
    {
        
        try
        {
            $divToDelete = Division::findOrFail($id);
            $divToDelete->delete();

            return redirect()->back()->with('success', 'Successfully Deleted.');
        }
        catch(Exception $e)
        {
            // Log the exception message if needed
            Log::error('Division Deletion Error: ' . $e->getMessage());
    
            // Return with an error message
            return redirect()->back()->with('error', 'Error occurred while trying to delete the department. Please try again.');
        }
    }

    public function addDivision()
    {   
        $allDepartments = Department::withCount('subjectCodes')->latest()->get();
        
        $existingDep = [];

        foreach($allDepartments as $dep)
        {
            if(!$dep->subject_codes_count)
            {   
                $existingDep[] = $dep;
            } 
        }
        
        
        return inertia('Dashboard/Division/DivisionAdd',[
            //'existingDepartment' => $existingDep,
            'existingDepartment' => $allDepartments,
        ]);
    }

    public function storeDivision(Request $request)
    {
        try{

            DB::beginTransaction();

            $newDivision = new Division();

            $newDivision->name = $request->divisionName;
            $newDivision->department_id = $request->departmentId;
            $newDivision->save();

            DB::commit();

        return redirect()->back()->with('success','Successfully created new division.');
        }catch(Exception $e){

            DB::rollback();

            // Log the error details
            Log::error('Failed to create new division', [
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'input_data' => $request->all()
            ]);
            return redirect()->back()-with('error', 'Failed to create new division. Please try again.');
        } 
    }

    public function updateShow($id)
    {
        $divToUpdate = Division::findOrFail($id);
        $departments = Department::latest()->get();

        return inertia('Dashboard/Division/DivisionUpdate',[
            'division' => $divToUpdate,
            'departments' => $departments,
        ]);
    }

    public function storeUpdate(Request $request)
    {
        // const form = useForm({
        //     divisionName:data.division ? data.division.name : '',
        //      divisionId:data.division ? data.division.id: '',
        //     departmentId:data.division ? data.division.department_id : '',
        // })

        try
        {
            DB::beginTransaction();

            $divToUpdate = Division::findOrFail($request->divisionId);

            $divToUpdate->name = $request->divisionName;
            $divToUpdate->department_id = $request->departmentId;
            $divToUpdate->save();

            DB::commit();
            return redirect()->route('division.show')->with('success', 'Successfully Updated!');
        }
        catch(Exception $e)
        {
            DB::rollback();

            // Log the error details
            Log::error('Failed to create new division', [
                'error' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'input_data' => $request->all()
            ]);

            return redirect()->back()-with('error', 'Failed to update division. Please try again!');
        }
        
    }

    public function import(Request $request)
    {
       try
       {
            DB::beginTransaction();
            $departmentId = $request->department_id;

            Excel::import(new ExcelDivisionImport($departmentId), $request->file('file'));
            DB::commit();

            return redirect()->back()->with('success', 'Successfully imported new Divisions.');
       }
       catch(\Exception $e)
       {
            DB::rollback();

            Log::info('error importing divisions: '.$e);

            return redirect()->back()->with('error','Failed to import Divisions.');
       }
    }

    public function downloadExcelFormat()
    {
        $filePath = '/excel_format/division_import_format.xlsx';

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
