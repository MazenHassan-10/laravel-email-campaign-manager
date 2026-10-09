<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lead extends Model
{
    protected $fillable = [
        'name',
        'email',
        'status',
        'country',
        'birthdate',
    ];
    
    public function campaigns() : BelongsToMany
    {
        return $this->belongsToMany(Campaign::class , 'campaign_leads')
            ->withPivot(['status' , 'attempts', 'sent_at'])
            ->withTimestamps();
    }
}
