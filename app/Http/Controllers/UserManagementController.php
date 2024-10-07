<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Division;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\DefaultPassword;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class UserManagementController extends Controller
{
    public function showUsers()
    {
       
        $departments = Department::with('divisions')->latest()->get();
        $users = User::with('department', 'division')->latest()->get();

        return inertia('Dashboard/Users/UsersAll',[
            'users'         => $users,
            'departments'   => $departments,
        ]);
    }

    public function showAddUser()
    {
        $departments = Department::with('divisions')->latest()->get();
        $defPass = DefaultPassword::first();
        
       
        return inertia('Dashboard/Users/UsersAdd',[
            'departments' => $departments,
            'defaultPw' => $defPass,
        ]);
    }

    public function storeUser(Request $request)
    {   
        $pass = DefaultPassword::first();
        $defPass = $pass->password;
        $defaultPassword = Hash::make($defPass);
        $newUser = new User();
        
        $request->validate([
            'email' => 'required|email|unique:users,email',
        ]);

        try
        {
            DB::beginTransaction();

            $newUser->name = $request->name;
            $newUser->email = $request->email;
            $newUser->password = $defaultPassword;
            $newUser->role = $request->role;
            $newUser->department_id = $request->department;
            $newUser->division_id = $request->division_id; 
            $newUser->save();

            DB::commit();
            
        }
        catch(Exception $e)
        {
            DB::rollback();
            Log::error('error creating new user: '.$e->getMessag());

            return redirect()->back()->with('error', 'Failed to create new user!');
        }
        
       
        //return redirect()->route('users.show')->with('success', 'Successfully created new user');

        return redirect()->back()->with('success', 'Successfully created new user');
    }

    public function updateUserShow($id)
    {
        $departments = Department::with('divisions')->latest()->get();
        $userToUpdate = User::findOrFail($id);
        return inertia('Dashboard/Users/UserUpdate',[
            'userToUpdate' => $userToUpdate,
            'departments' => $departments,
        ]);
    }

    public function updateUserStore(Request $request)
    {
        //dd($request);
        $userToUpdate = User::findOrFail($request->id);

        // Validate the request, ignoring the current user's email
        $request->validate([
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($userToUpdate->id),
            ],
            // Add other validation rules as needed
        ]);
 
        try
        {
            DB::beginTransaction();

            $userToUpdate->name = $request->name;
            $userToUpdate->email = $request->email;
            $userToUpdate->role = $request->role;
            $userToUpdate->department_id = $request->department;
            $userToUpdate->division_id = $request->division_id; 
            $userToUpdate->save();

            DB::commit();
            
        }
        catch(Exception $e)
        {
            DB::rollback();
            Log::error('error creating new user: '.$e->getMessag());

            return redirect()->back()->with('error', 'Failed to update user!');
        }
        

        return redirect()->route('users.show')->with('success', 'Updated Successfully.');
    }

    public function deleteUser($id)
    {
        $loggedUserId = Auth::user()->id;

        if($id == $loggedUserId)
        {
            return redirect()->back()->with('error', 'You cannot delete yourself.');
        }
        else
        {
            try
            {
                DB::beginTransaction();

                $user = User::findOrFail($id);
                $user->delete();

                DB::commit();
                return redirect()->back()->with('success', 'Successfully Deleted a User.');
            }
            catch(\Exception $e)
            {
                DB::rollback();
                Log::error('error deleting user: '.$e->getMessage());

                return redirect()->back()->with('error', 'Error Deleting a User');
            }
        }

        

    }

    // excel import logic

    public function storeExcelUsers(Request $request){
        
         // Get the uploaded file
         $file = $request->file('file');

         // Load the Excel file and read its contents
         $data = Excel::toArray([], $file);
        
         
         // Dump and die to output the data
        // dd($data[0]);
        //dd(count($data[0]));
        try
        {
            DB::beginTransaction();
            foreach($data[0] as $index => $user )
            {
               $existingDepartments = Department::with('divisions')->get();
               
               //dd($existingDepartments);
               //dd(count($existingDepartments[2]->divisions));
   
               if($index == 0)
               {
                   continue;
                  
               }
   
               //dd($user[0].' '.$user[1].' '.$user[2].' '.$user[3].' '.$user[4]);
               
   
               if(strlen($user[0]) > 50 || $user[0] == '')
               {
                   return redirect()->back()->with('error', 'The name field is required and must not exceed 50 characters.');
               }
   
               // $email = "test@example.com";
   
               if (!filter_var($user[1], FILTER_VALIDATE_EMAIL)) {
   
                   return redirect()->back()->with('error', "The email field is required and must be a valid email for each user.");
               } 
               
               $existingRoles = ['admin', 'co-admin', 'department head', 'faculty'];
   
               if (!in_array(strtolower($user[2]), $existingRoles)) //strtolower($existingRoles)
               {
                   return redirect()->back()->with('error', 'User role is not valid. Valid roles: "admin, co-admin, department head, faculty"');
               }
   
               
               // departments and division logic
               
               $name = $user[0];
               $email = $user[1];
               $role = $user[2];
               $userDepartmenId = '';
               $userDivisionId = '';
               
               
               if(strtolower($user[2] == 'department head') ) // dep head
               {
                   
                   if(strtolower($user[3] == '' ))
                   {
                       return redirect()->back()->with('error', 'Department field is required for Dep Head or faculty');
                   }
   
                   $userDepHeadDepartment = Department::whereRaw('LOWER(name) = ?', [strtolower($user[3])])->first();
   
                   if(!$userDepHeadDepartment)
                   {
                       return redirect()->back()->with('error', $user[3].' Department name do not exist');
                   }
   
                   $userDepartmenId = $userDepHeadDepartment->id;
                   $userDivisionId  = null;
               }
   
   
               if(strtolower($user[2] == 'faculty')) // faculty
               {
                   if(strtolower($user[3] == '' ))
                   {
                       return redirect()->back()->with('error', 'Department field is required for Dep Head or faculty');
                   }
   
                   $userFacultyDepartment = Department::with('divisions')->whereRaw('LOWER(name) = ?', [strtolower($user[3])])->first();
   
                   if(!$userFacultyDepartment)
                   {
                       return redirect()->back()->with('error', $user[3].' Department do not exist');
                   }
                   $userDepartmenId = $userFacultyDepartment->id;
                   //check if the department have divisions 
                   $hasDivision = count($userFacultyDepartment->divisions);

                   if($hasDivision == 0)
                   {
                        $userDivisionId = null;
                   }  
                   
                   if($hasDivision != 0)  // if department has division, division field should be required
                   {
                      if(!$user[4])
                      {
                         return redirect()->back()->with('error','Division is required for '.$user[3].' department');
                      }
   
                      $existingDivision = Division::whereRaw('Lower(name) = ?', [strtolower($user[4])])->first();
   
                      if(!$existingDivision)
                      {
                        return redirect()->back()->with('error', $user[4].' division do not exist');
                      }
   
                      $userDivisionId = $existingDivision->id;
                   }
                  
                                     
               }
   
               // $name = $user[0];
               // $email = $user[1];
               // $role = $user[2];
               // $userDepartmenId = '';
               // $userDivisionId = '';
               
               //dd('name: '.$name.' >>email: '.$email.' >>role: '.$role.' >>department_id: '.$userDepartmenId.' >>division_id: '.$userDivisionId);
               $pass = DefaultPassword::first();
               $defPass = $pass->password;
               $defaultPassword = Hash::make($defPass);
   
               $newUser                    = new User();
               $newUser->name              = $name;
               $newUser->email             = $email;
               $newUser->password          = $defaultPassword;
               $newUser->role              = $role;
               if($role == 'admin' || $role == 'co-admin')
               {
                    $newUser->department_id     = null;
                    $newUser->division_id       = null;
               }
               else
               {
                    $newUser->department_id     = $userDepartmenId;
                    $newUser->division_id       = $userDivisionId;
               }

               $newUser->created_at = Carbon::now();
               $newUser->save();
 
            }
            
            DB::commit();
            return redirect()->route('users.show')->with('success', 'Successfully created new Users');
        } 
        catch(Exception $e)
        {
            DB::rollback();
            Log::error('error uploading excel users: '.$e->getMessage());

            return redirect()->back()->with('error', 'Error excel upload.');
        }
        
        
    }
}
