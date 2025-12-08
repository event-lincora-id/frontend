<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventBookmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'category',
        'notes',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // Scopes
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeUpcoming($query)
    {
        return $query->whereHas('event', function($q) {
            $q->where('start_date', '>', now());
        });
    }

    public function scopePast($query)
    {
        return $query->whereHas('event', function($q) {
            $q->where('end_date', '<', now());
        });
    }
}