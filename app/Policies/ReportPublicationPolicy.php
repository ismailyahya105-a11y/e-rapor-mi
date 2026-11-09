<?php

namespace App\Policies;

use App\Models\ReportPublication;
use App\Models\User;

class ReportPublicationPolicy
{
    /**
     * Determine whether the user can view the report publication.
     */
    public function view(User $user, ReportPublication $reportPublication): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isWali()) {
            return $user->students()->where('students.id', $reportPublication->student_id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can download the report publication.
     */
    public function download(User $user, ReportPublication $reportPublication): bool
    {
        return $this->view($user, $reportPublication);
    }
}
