<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeroomTeacherAssignment;
use App\Models\SchoolClass;
use App\Models\SchoolProfile;
use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolProfileController extends Controller
{
    public function index(): View
    {
        return view('admin.school-profile.index', [
            'profile' => SchoolProfile::query()->first(),
            'semesters' => Semester::query()->with('academicYear')->get(),
            'classes' => SchoolClass::query()->with('academicYear')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $profile = SchoolProfile::query()->firstOrNew();
        $profile->fill($request->validate(['name' => ['required', 'string'], 'npsn' => ['required', 'string'], 'address' => ['required', 'string'], 'principal_name' => ['required', 'string'], 'nsm' => ['nullable', 'string'], 'principal_nip' => ['nullable', 'string']]))->save();

        return back()->with('status', 'Profil madrasah tersimpan.');
    }

    public function assign(Request $request): RedirectResponse
    {
        $data = $request->validate(['school_class_id' => ['required', 'exists:school_classes,id'], 'semester_id' => ['required', 'exists:semesters,id'], 'teacher_name' => ['required', 'string'], 'teacher_nip' => ['nullable', 'string']]);
        HomeroomTeacherAssignment::query()->updateOrCreate(['school_class_id' => $data['school_class_id'], 'semester_id' => $data['semester_id']], $data);

        return back()->with('status', 'Wali kelas tersimpan.');
    }
}
