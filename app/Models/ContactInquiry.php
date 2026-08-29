<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactInquiry extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'subject',
        'question',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
