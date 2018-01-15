<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(User $user)
    {

        $activities = $this->getActivity($user);
        return view('profiles.show', [
            'profileUser' => $user,
            'activities' => $activities
        ]);
    }

    /**
     * @param User $user
     * @return mixed
     */
    protected function getActivity(User $user)
    {
        $activities = $user->activity()->latest()->take(50)->with('subject')->get()->groupBy(function ($activity) {
            return $activity->created_at->format('Y-m-d');
        });
        return $activities;
    }
}
