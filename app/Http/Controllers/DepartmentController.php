<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ExcelDepartmentImport;
use Illuminate\Support\Facades\Storage;

class DepartmentController extends Controller
{
    public function showDepartment()
    {
        
        return inertia('Dashboard/Department/DepartmentAll', [
                'departments' => Department::with([
                    'departmentHeads' => function ($query) {
                    $query->where('role', 'department head');
                },
                'faculty' => function ($query) {
                    $query->where('role', 'faculty');
                },
                'divisions',
            ])->latest()->get(),
        ]);
    }

    public function destroy($id)
    {
        
        try {
            $depTodelete = Department::findOrFail($id);
            $depTodelete->delete();
    
            return redirect()->back()->with('success', 'Successfully deleted!');
        } catch (\Exception $e) {
            // Log the exception message if needed
            Log::error('Department Deletion Error: ' . $e->getMessage());
    
            // Return with an error message
            return redirect()->back()->with('error', 'Error occurred while trying to delete the department. Please try again.');
        }
    }

    public function addDepartment()
    {
        
        return inertia('Dashboard/Department/DepartmentAdd');
    }

    public function storeDepartment(Request $request)
    {
        $request->validate([
            'departmentName' => 'required|unique:departments,name',
        ]);

        try{
            DB::beginTransaction();
            $newDepartment = new Department();

            $newDepartment->name = $request->departmentName;
            $newDepartment->save();

            // Simulate an error
            //throw new \Exception('Simulated error');

            DB::commit();
            return redirect()->route('departments.show')->with('success', 'Successfully created new department.');
        }catch(\Exception $e)
        {
            DB::rollback();

            // Log the exception message if needed
            Log::error('Failed to create new department error: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to create new department');
        }
        
    }

    public function updateDepartmentShow($id)
    {
        $department = Department::findOrFail($id);
        
        return inertia('Dashboard/Department/DepartmentUpdate',[
            'department' => $department, 
        ]);
    }
    public function updateDepartment(Request $request)
    {
        //dd($request->departmentId);
        $request->validate([
            'departmentName' => [
                'required',
                Rule::unique('departments', 'name')->ignore($request->departmentId),
            ],
        ]);

        try{
            DB::beginTransaction();

            $depToUpdate = Department::findOrFail($request->departmentId);

            $depToUpdate->name = $request->departmentName;
            $depToUpdate->save();

            //throw new \Exception('simulated error'); 
            DB::commit();

            return redirect()->route('departments.show')->with('success', 'Updated Successfully.');
        }catch(\Exception $e)
        {
            DB::rollback();
            Log::error('Failed to update department:  '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to update department. ');
        }
        
    }

    public function import(Request $request)
    {

       try
       {    
            DB::beginTransaction();
            Excel::import(new ExcelDepartmentImport(), $request->file('file'));

            DB::commit();

            return redirect()->back()->with('success', 'Successfully imported new Departments');

       }
       catch(\Exception $e)
       {
            DB::rollback();
            Log::info('Error importing excel departments: '.$e);

            return redirect()->back()->with('error', 'Failed to import Departments');
       }

       


    }

    public function downloadExcelFormat()
    {
        $filePath = '/excel_format/department_import_format.xlsx';

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
