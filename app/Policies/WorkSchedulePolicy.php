<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkSchedulePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return in_array($user->role, ['admin', 'audit', 'leader']);
    }

    public function view(User $user, WorkSchedule $workSchedule)
    {
        return in_array($user->role, ['admin', 'audit', 'leader']);
    }

    public function create(User $user)
    {
        return in_array($user->role, ['admin', 'audit']);
    }

    public function update(User $user, WorkSchedule $workSchedule)
    {
        return in_array($user->role, ['admin', 'audit']);
    }

    public function delete(User $user, WorkSchedule $workSchedule)
    {
        return in_array($user->role, ['admin']);
    }

    public function access_work_schedules(User $user)
    {
        return in_array($user->role, ['admin', 'audit', 'leader']);
    }
}