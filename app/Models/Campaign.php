<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Campaign extends Model
{
    protected $fillable = [
        'subject',
        'body',
        'status',
    ];
    
    public function leads() : BelongsToMany
    {
        return $this->belongsToMany(Lead::class , 'campaign_leads')
            ->withPivot(['status' , 'attempts', 'sent_at'])
            ->withTimestamps();
    }
}
