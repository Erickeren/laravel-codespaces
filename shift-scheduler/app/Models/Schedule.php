<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Schedule extends Model
{
    use HasFactory;
    
    protected $fillable = ['date', 'person_id', 'shift_type', 'status'];
    
    protected $casts = [
        'date' => 'date',
    ];
    
    public function person()
    {
        return $this->belongsTo(Person::class);
    }
    
    public function getShiftTimeAttribute()
    {
        if ($this->shift_type === 'A') {
            return '6:00 AM - 6:00 PM';
        } else {
            return '6:00 PM - 6:00 AM+1';
        }
    }
}
