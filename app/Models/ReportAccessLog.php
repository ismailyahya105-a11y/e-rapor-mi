<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['report_publication_id', 'user_id', 'action', 'ip_address', 'user_agent', 'downloaded_at'])]
class ReportAccessLog extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'downloaded_at' => 'datetime',
        ];
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(ReportPublication::class, 'report_publication_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getPublicationIdAttribute(): int
    {
        return (int) $this->report_publication_id;
    }
}
