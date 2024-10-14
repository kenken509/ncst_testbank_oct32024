<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Question;
use App\Models\Department;
use App\Models\SubjectCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function showDashboard()
    {
        $adminsCount    = User::where('role','admin')->count();
        $depHeadCount   = User::where('role','department head')->count();
        $facultyCount   = User::where('role', 'faculty')->count();
        $coAdminsCount  = User::where('role', 'co-admin')->count();
        $usersCount     = User::all()->count();
        $questionsCount = Question::all()->count();
        // get all the subject codes with empty questions per department

        $emptySubjectCodes = $emptySubjectCodes = Department::with(['subjectCodes' => function ($query) {
                                $query->whereDoesntHave('questions');
                            }])->get();
        
        
        $userQuestionCount = User::withCount('questions')
                                ->orderBy('questions_count','asc')
                                ->take(10)
                                ->get();
        
        return inertia('Dashboard/Dashboard/Dashboard',[
            'adminsCount'       => $adminsCount,
            'depHeadCount'      => $depHeadCount,
            'facultyCount'      => $facultyCount,
            'questionsCount'    => $questionsCount,
            'userQuestionCount' => $userQuestionCount,
            'coAdminsCount'     => $coAdminsCount,
            'usersCount'        => $usersCount,
            'emptySubjectCodes' => $emptySubjectCodes,
        ]);
    }

    public function showDepHeadDashboard()
    {
        $user = Auth::user();
        $userDepId = $user->department_id;
        
        $department = Department::where('id', $userDepId)->with('divisions')->first();
        $hasDivision = false;
        
        if(count($department->divisions))
        {
            $emptySubjectCodes = Department::where('id', $userDepId)->with(['divisions' => function ($query){
                $query->with(['divSubjectCodes' => function ($query){
                    $query->whereDoesntHave('questions');
                }]);
            }])->get();
            
            //dd($emptySubjectCodes);
            $hasDivision = true;
        }
        else
        {
            $emptySubjectCodes = Department::where('id', $userDepId)->with(['subjectCodes' => function ($query) {
                $query->whereDoesntHave('questions');
            }])->get();
        }
        
        $facultyCount = User::where('department_id', $userDepId)->where('role', 'faculty')->count();

        $questionsCount = Question::whereHas('subjectCode', function ($query) use ($userDepId) {
            $query->where('department_id', $userDepId);
        })->count();
        

        $userQuestionCount = User::where('department_id', $userDepId) // Filter by department_id
                            ->withCount('questions') // Count the associated questions
                            ->orderBy('questions_count', 'asc') // Order by the question count in ascending order
                            ->take(10) // Limit to 10 users
                            ->get();

       

        

        return inertia('Dashboard/Dashboard/DepHeadDashboard',[
            'facultyCount'          => $facultyCount,
            'questionsCount'        => $questionsCount,
            'userQuestionCount'     => $userQuestionCount,
            'emptySubjectCodes'     => $emptySubjectCodes,
            'hasDivisions'          => $hasDivision,
        ]);
    }
}
