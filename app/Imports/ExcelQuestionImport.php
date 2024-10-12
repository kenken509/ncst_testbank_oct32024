<?php

namespace App\Imports;

use App\Models\Question;
use App\Models\Option;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExcelQuestionImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    protected $subjectCodeId, $term, $authorId;

    public function __construct($subjectCodeId, $term, $authorId)
    {
        $this->subjectCodeId    = $subjectCodeId;
        $this->term             = $term;
        $this->authorId         = $authorId;
    }

    public function model(array $row)
    {
        $question =  Question::create([
            'question'          => $row['question'],
            'type'              => 'text',
            'attached_image'    => null,
            'term'              => $this->term,
            'subject_code_id'   => $this->subjectCodeId,  
            'author_id'         => $this->authorId,  
            'created_at'        => now(),

        ]);
        
        Option::create(['question_id' => $question->id, 'option' => $row['option_a'], 'isCorrect' => 'false']);
        Option::create(['question_id' => $question->id, 'option' => $row['option_b'], 'isCorrect' => 'false']);
        Option::create(['question_id' => $question->id, 'option' => $row['option_c'], 'isCorrect' => 'false']);
        Option::create(['question_id' => $question->id, 'option' => $row['correct_answer'], 'isCorrect' => 'true']);


        return $question;
    }
}
