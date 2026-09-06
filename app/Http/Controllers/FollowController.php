<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FollowController extends Controller
{
    public function toggle(Request $request, int $userId)
    {
        if (! Schema::hasTable('follows')) {
            return back()->with('error', 'Run DB migrations first (Admin → Run DB migrations).');
        }

        $target = User::findOrFail($userId);
        $me = $request->user()->id;

        if ($me === $target->id) {
            return back()->with('error', 'You cannot follow yourself.');
        }

        $exists = DB::table('follows')
            ->where('follower_id', $me)
            ->where('following_id', $target->id)
            ->first();

        if ($exists) {
            DB::table('follows')->where('id', $exists->id)->delete();

            return back()->with('success', 'Unfollowed '.$target->name);
        }

        DB::table('follows')->insert([
            'follower_id' => $me,
            'following_id' => $target->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Following '.$target->name);
    }
}
