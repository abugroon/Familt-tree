<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    protected $fillable = [
        'name',
        'description',
        'gender',
        'birth_date',
        'death_date',
        'photo',
        'parent_id',
        'user_id',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'death_date' => 'date',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Person::class, 'parent_id');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo) return null;
        return asset('storage/' . $this->photo);
    }

    protected $appends = ['photo_url'];
}
