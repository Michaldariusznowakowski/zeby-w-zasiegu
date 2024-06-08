<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProfileController extends Controller
{
    function index()
    {
        return view('profile.index');
    }
    function edit(Request $request)
    {
        $request->validate([
            'name' => 'string | max:200 | required | min:3',
            'surname' => 'string | max:200 | required | min:3',
            'phone_number' => 'numeric | required | min:9',
        ]);
        $user = User::find(Auth::id());
        if (!$user) {
            return redirect()->route('login');
        }
        $user->name = $request->name;
        $user->surname = $request->surname;
        $user->phone_number = $request->phone_number;
        $user->save();
        return redirect()->route('profile')->with('success', 'Dane zostały zaktualizowane');
    }
}
