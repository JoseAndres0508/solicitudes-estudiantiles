<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['year', 'term_number', 'start_date', 'end_date'])]
class AcademicTerm extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date'];
    }

    /** @return HasMany<AcademicRecord, $this> */
    public function academicRecords(): HasMany
    {
        return $this->hasMany(AcademicRecord::class);
    }
}
