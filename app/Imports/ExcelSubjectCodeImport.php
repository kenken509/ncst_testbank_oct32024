<?php

namespace App\Imports;

use App\Models\SubjectCode;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExcelSubjectCodeImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    protected $department_id, $division_id;

    public function __construct($department_id, $division_id)
    {
        $this->department_id = $department_id;
        $this->divisioin_id = $division_id;

    }

    public function model(array $row)
    {
        

        
        $subjectCode = SubjectCode::create([
            'name'          => $row['name'],
            'description'   => $row['description'],
            'department_id' => $this->department_id,
            'division_id'   => $this->division_id,
            'created_at'    => now(),
        ]);
        // return new SubjectCode([
            
        // ]);

        return $subjectCode;
    }
}
