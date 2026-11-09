<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[Fillable(['academic_year_id', 'name', 'is_active', 'is_locked', 'locked_at', 'locked_by_user_id'])]
class Semester extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_locked' => 'boolean',
            'locked_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<AcademicYear, $this>
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by_user_id');
    }

    public function lock(?User $by = null): void
    {
        $this->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by_user_id' => $by?->id,
        ]);
    }

    public function unlock(): void
    {
        $this->update([
            'is_locked' => false,
            'locked_at' => null,
            'locked_by_user_id' => null,
        ]);
    }

    public function isLocked(): bool
    {
        return (bool) $this->is_locked;
    }

    /**
     * @return HasMany<Enrollment, $this>
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * @return HasMany<SubjectAssignment, $this>
     */
    public function subjectAssignments(): HasMany
    {
        return $this->hasMany(SubjectAssignment::class);
    }

    /**
     * @return HasMany<AssessmentComponent, $this>
     */
    public function assessmentComponents(): HasMany
    {
        return $this->hasMany(AssessmentComponent::class);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Activate this semester and its academic year, deactivating all other semesters.
     */
    public function activate(): void
    {
        DB::transaction(function (): void {
            self::query()->update(['is_active' => false]);
            AcademicYear::query()->update(['is_active' => false]);

            $this->update(['is_active' => true]);
            $this->academicYear()->update(['is_active' => true]);
        });
    }

    public function getFullNameAttribute(): string
    {
        $yearName = $this->academicYear?->name ?? '-';

        return "{$yearName} — Semester {$this->name}";
    }
}
