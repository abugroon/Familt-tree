<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Marriage extends Model
{
    protected $fillable = ['person_id', 'spouse_id', 'marriage_date'];

    protected $casts = ['marriage_date' => 'date'];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    public function spouseRelation(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'spouse_id');
    }
}
