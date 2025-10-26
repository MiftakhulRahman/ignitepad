<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (!$query) {
            return response()->json([]);
        }

        $users = User::where('name', 'like', "%{$query}%")
                     ->orWhere('email', 'like', "%{$query}%")
                     ->select('id', 'name', 'email')
                     ->get();

        return response()->json($users);
    }
}
