<?php

namespace App\Observers;

use App\Models\GroupUser;
use Illuminate\Support\Facades\Cache;

class GroupUserObserver
{
    /**
     * Handle the GroupUser "created" event.
     *
     * @param  \App\Models\GroupUser  $groupUser
     * @return void
     */
    public function created(GroupUser $groupUser)
    {
        Cache::forget($groupUser->group_id);
    }

    /**
     * Handle the GroupUser "updated" event.
     *
     * @param  \App\Models\GroupUser  $groupUser
     * @return void
     */
    public function updated(GroupUser $groupUser)
    {
        Cache::forget($groupUser->group_id);
    }

    /**
     * Handle the GroupUser "deleted" event.
     *
     * @param  \App\Models\GroupUser  $groupUser
     * @return void
     */
    public function deleted(GroupUser $groupUser)
    {
        Cache::forget($groupUser->group_id);
    }

    /**
     * Handle the GroupUser "restored" event.
     *
     * @param  \App\Models\GroupUser  $groupUser
     * @return void
     */
    public function restored(GroupUser $groupUser)
    {
        Cache::forget($groupUser->group_id);
    }

    /**
     * Handle the GroupUser "force deleted" event.
     *
     * @param  \App\Models\GroupUser  $groupUser
     * @return void
     */
    public function forceDeleted(GroupUser $groupUser)
    {
        Cache::forget($groupUser->group_id);
    }
}