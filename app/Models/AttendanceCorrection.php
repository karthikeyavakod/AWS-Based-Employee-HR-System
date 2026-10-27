<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceCorrection extends Model
{
    protected $fillable = [
        'employee_id',
        'attendance_date',
        'requested_check_in_at',
        'requested_check_out_at',
        'requested_status',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'reviewer_comments',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'requested_check_in_at' => 'datetime',
        'requested_check_out_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}