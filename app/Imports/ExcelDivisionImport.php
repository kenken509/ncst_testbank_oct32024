<?php

namespace App\Imports;

use App\Models\Division;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExcelDivisionImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    protected $department_id;

    public function __construct($department_id)
    {
        $this->department_id = $department_id;
    }

    public function model(array $row)
    {
        $division = Division::create([
            'name'          => $row['name'],
            'department_id' => $this->department_id,
            'created_at'    => now(),
        ]);

        return $division;
    }
}
