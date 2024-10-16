<?php

namespace App\Imports;

use App\Models\User;
use App\Models\DefaultPassword;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;

use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExcelUserImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    protected $department_id, $division_id;
    public function __construct($department_id, $division_id)
    {
        $this->department_id    = $department_id;
        $this->division_id      = $division_id;
    }


    public function model(array $row)
    {
        
        // Get the current row number
        //$rowNumber = $row->getRow();
        $pass = DefaultPassword::first();
        $defPass = $pass->password;
        $defaultPassword = Hash::make($defPass);

        // Log the row for debugging
        Log::info('Row data: ', $row);
        
        // Skip rows that don't have all required keys
        if (!isset($row['name'], $row['email'], $row['role'])) {
            Log::warning('Skipping row due to missing keys: ', $row);
            return null; // Skip this row
        }
        // Skip empty rows
        // if (empty(array_filter($row))) {
        //     return null; // Or you can choose to log this if needed
        // }

        if (empty($row['name'])) {
            throw ValidationException::withMessages([
                'name' => 'The name is required!!!!',
            ]);
            
        }

        if (strlen($row['name']) > 50  ) {
            throw ValidationException::withMessages([
                'name' => 'The name must not exceed 70 characters.',
            ]);
        }

        

        if (empty($row['role'])) {
            throw ValidationException::withMessages([
                'role' => 'The role is required',
            ]);
        }

        //>>>>>>>>>>>>>>>>>>>
        

        if ($row['email'] == '') {
            throw ValidationException::withMessages([
                'email' => 'The email is required',
            ]);
        }
        //validate email
        if (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'email' => "Email is not valid.",
            ]);
        }

         // Check for uniqueness
         if (User::where('email', $row['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => "Email must be unique.",
            ]);
        }

        


        $user = User::create([
            'name'          => $row['name'],
            'email'         => $row['email'],
            'role'          => $row['role'],
            'department_id' => $this->department_id,
            'division_id'   => $this->division_id,
            'password'      => $defaultPassword,
            'created_at'    => now(),
        ]);
        
        return $user;
    }
}
