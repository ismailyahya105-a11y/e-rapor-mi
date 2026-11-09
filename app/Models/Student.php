<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nis',
    'nisn',
    'name',
    'gender',
    'birth_place',
    'birth_date',
    'address',
    'status',
])]
class Student extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
        ];
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * @return BelongsToMany<SchoolClass, $this>
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'enrollments');
    }

    /**
     * @return HasMany<GuardianStudent, $this>
     */
    public function guardianStudents(): HasMany
    {
        return $this->hasMany(GuardianStudent::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'guardian_students', 'student_id', 'user_id')
            ->withPivot('relationship')
            ->withTimestamps();
    }

    /**
     * @return HasMany<StudentAssessmentScore, $this>
     */
    public function assessmentScores(): HasMany
    {
        return $this->hasMany(StudentAssessmentScore::class);
    }

    /**
     * @return HasMany<StudentSubjectResult, $this>
     */
    public function subjectResults(): HasMany
    {
        return $this->hasMany(StudentSubjectResult::class);
    }

    /**
     * @return HasMany<StudentAttitudeRating, $this>
     */
    public function attitudeRatings(): HasMany
    {
        return $this->hasMany(StudentAttitudeRating::class);
    }

    /**
     * @return HasMany<StudentAttitudeSummary, $this>
     */
    public function attitudeSummaries(): HasMany
    {
        return $this->hasMany(StudentAttitudeSummary::class);
    }

    /**
     * @return HasMany<StudentAttendance, $this>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(StudentAttendance::class);
    }

    /**
     * @return HasMany<StudentActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(StudentActivity::class);
    }

    /**
     * @return HasMany<StudentExtracurricular, $this>
     */
    public function extracurriculars(): HasMany
    {
        return $this->hasMany(StudentExtracurricular::class);
    }

    /**
     * @return HasMany<StudentAchievement, $this>
     */
    public function achievements(): HasMany
    {
        return $this->hasMany(StudentAchievement::class);
    }

    /**
     * @return HasMany<HomeroomNote, $this>
     */
    public function homeroomNotes(): HasMany
    {
        return $this->hasMany(HomeroomNote::class);
    }

    /**
     * @return HasMany<ReportPublication, $this>
     */
    public function reportPublications(): HasMany
    {
        return $this->hasMany(ReportPublication::class);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    public function currentEnrollment(?Semester $semester = null): ?Enrollment
    {
        $targetSemester = $semester ?? Semester::query()->where('is_active', true)->first();

        if (! $targetSemester) {
            return null;
        }

        return $this->enrollments()
            ->where('semester_id', $targetSemester->id)
            ->with(['schoolClass.academicYear'])
            ->first();
    }

    public function currentClass(?Semester $semester = null): ?SchoolClass
    {
        return $this->currentEnrollment($semester)?->schoolClass;
    }
}
