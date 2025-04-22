<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{


    public function __construct()
    {
        if (Auth::user()->role != 1) {
            abort(404);
        }
    }

    public function index()
    {
        $users = User::where('id','!=',Auth::user()->id)->latest()->paginate(15);
        return view('admin.user',compact('users'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            // 'port' => 'required|numeric',
        ]);

        $user = User::find($id);
        $user->name = $request->name;
        $user->email = $request->email;
        // $user->port = $request->port;
        $user->save();
        return redirect()->back()->with('success', 'User updated successfully');
    }

    public function status($id)
    {
        $user = User::find($id);
        
        if ($user->id != Auth::user()->id) {
            $user->status = $user->status == 1 ? 0 : 1;
            $user->save();
            return redirect()->back()->with('success', 'User status updated successfully');
        }else {
            return redirect()->back()->with('error', 'You can not update your status');
        }
    }


    public function destroy($id)
    {
        $user = User::find($id);
        
        if ($user->id != Auth::user()->id) {
            $user->delete();
            return response()->json([
                'status' => true,
                'message' => 'User deleted successfully'
            ], 202);
        }else {
            return response()->json([
                'status' => false,
                'message' => 'You can not delete yourself'
            ], 400);
        }
    }
}
