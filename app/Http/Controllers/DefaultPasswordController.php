<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DefaultPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DefaultPasswordController extends Controller
{
    public function updateDefaultPassword(Request $request){
        $newPassword = DefaultPassword::first();

        try
        {
            DB::beginTransaction();
            $newPassword->password = $request->password;
            $newPassword->save();

            DB::commit();
            return redirect()->back()->with('success', 'Successfully updated default password.');
        }catch(\Exception $e)
        {
            
            DB::rollback();

            // Log the exception message if needed
            Log::error('Failed to update default password: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Failed to update default password');
        }
        
        

    

    }
}
