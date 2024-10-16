<?php

namespace App\Imports;

use App\Models\Department;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExcelDepartmentImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    
    public function model(array $row)
    {

        if (strlen($row['name']) > 70) {
            throw ValidationException::withMessages([
                'name' => 'The name must not exceed 70 characters.',
            ]);
        }

        // Check for uniqueness
        if (Department::where('name', $row['name'])->exists()) {
            throw ValidationException::withMessages([
                'name' => 'The name must be unique.',
            ]);
        }

        $department = Department::create([
            'name' => $row['name'],
            'created_at' => now(),
        ]);

        return $department;
    }
}
