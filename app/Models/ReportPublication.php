<?php

namespace App\Models;

use Database\Factories\ReportPublicationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['report_draft_id', 'student_id', 'semester_id', 'published_by_user_id', 'snapshot', 'pdf_path', 'published_at'])]
class ReportPublication extends Model
{
    /** @use HasFactory<ReportPublicationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['snapshot' => 'array', 'published_at' => 'datetime'];
    }

    public function draft(): BelongsTo
    {
        return $this->belongsTo(ReportDraft::class, 'report_draft_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by_user_id');
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(ReportAccessLog::class);
    }

    public function latestDownloadLog(): HasOne
    {
        return $this->hasOne(ReportAccessLog::class)->where('action', 'download')->latestOfMany();
    }

    public function isDownloadedByGuardian(): bool
    {
        return $this->accessLogs()->where('action', 'download')->exists();
    }
}
