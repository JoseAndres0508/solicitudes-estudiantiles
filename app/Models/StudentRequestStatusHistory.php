<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('student_request_status_history')]
#[Fillable(['student_request_id', 'previous_status', 'new_status', 'comment', 'user_id', 'notified_at'])]
class StudentRequestStatusHistory extends Model
{
    use HasFactory;

    /** The table only carries created_at. */
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['notified_at' => 'datetime'];
    }

    /** @return BelongsTo<StudentRequest, $this> */
    public function studentRequest(): BelongsTo
    {
        return $this->belongsTo(StudentRequest::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
