<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['subject_id', 'school_class_id', 'semester_id'])]
class SubjectAssignment extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<Subject, $this>
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * @return BelongsTo<SchoolClass, $this>
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    /**
     * @return BelongsTo<Semester, $this>
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
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
     * Get assessment components configured for this subject and semester.
     *
     * @return Collection<int, AssessmentComponent>
     */
    public function getAssessmentComponentsAttribute()
    {
        return AssessmentComponent::query()
            ->where('subject_id', $this->subject_id)
            ->where('semester_id', $this->semester_id)
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('id')
            ->get();
    }
}
