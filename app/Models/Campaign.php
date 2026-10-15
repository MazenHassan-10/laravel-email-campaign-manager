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
        'audience_filters'
    ];

    protected function casts() : array
    {
        return[
            'audience_filters'=>'array',
        ];
    }

    public function leads() : BelongsToMany
    {
        return $this->belongsToMany(Lead::class , 'campaign_leads')
            ->withPivot(['status' , 'attempts', 'sent_at'])
            ->withTimestamps();
    }

    public function matchingLeadsQuery()
    {
        $filters = $this->audience_filters ?? [];

        $query = Lead::query();


        if(filled($filters['filter_name'] ?? null ))
        {
            $query->where('name' , 'like' , $filters['filter_name'] . '%');
        }

        if(filled($filters['filter_email'] ?? null ))
        {
            $query->where('email' , 'like' , $filters['filter_email'] . '%');
        }

        if(filled($filters['filter_country'] ?? null ))
        {
            $query->where('country' ,$filters['filter_country']);
        }

        if(filled($filters['filter_status'] ?? null ))
        {
            $query->where('status' , $filters['filter_status']);
        }


        $today = now()->startOfDay();

        if(filled($filters['filter_age_min']??null))
            {
                $minBirthdate = $today->copy()->subYears( (int) $filters['filter_age_min'] );

                $query->whereDate('birthdate', '<=' , $minBirthdate);
            }

        if(filled($filters['filter_age_max']??null))
            {
                $maxBirthdate = $today->copy()->subYears( (int) $filters['filter_age_max'] +1 );

                $query->whereDate('birthdate', '>' , $maxBirthdate);
            }

        return $query;
    }
}
