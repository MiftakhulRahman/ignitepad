<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class CheckUsernameController extends Controller
{
    public function check(Request $request)
    {
        $username = $request->input('username');
        $ignoreId = $request->input('ignore_id');

        $query = User::where('username', $username);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        $isAvailable = !$query->exists();

        return response()->json(['available' => $isAvailable]);
    }
}
