<?php

namespace Database\Seeders;

use App\Models\Campaign;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{


    public function run(): void
    {
        $campaigns = [
            [
                'subject' => 'Welcome to our platform',
                'body' => 'Welcome! We are glad to have you with us.',
                'audience_filters' => [],
            ],
            [
                'subject' => 'Special offer for Egypt',
                'body' => 'Exclusive offer: a special discount for our Egyptian customers.',
                'audience_filters' => ['filter_country' => 'Egypt'],
            ],
            [
                'subject' => 'Follow up with new leads',
                'body' => 'Thanks for your interest. Can we help you with anything?',
                'audience_filters' => ['filter_status' => 'new'],
            ],
            [
                'subject' => 'Exclusive deal for interested customers',
                'body' => 'Limited time: take advantage of this deal before it ends.',
                'audience_filters' => [
                    'filter_status' => 'interested',
                    'filter_country' => 'Saudi Arabia',
                ],
            ],
            [
                'subject' => 'Young adults campaign',
                'body' => 'Made for you: check out what is new this month.',
                'audience_filters' => [
                    'filter_age_min' => 18,
                    'filter_age_max' => 30,
                ],
            ],
            [
                'subject' => 'Thank you for being with us',
                'body' => 'As a valued customer, here is a small gift from us.',
                'audience_filters' => [
                    'filter_status' => 'converted',
                    'filter_age_min' => 30,
                ],
            ],
        ];

        foreach ($campaigns as $campaign) {
            Campaign::create($campaign + ['status' => 'pending']);
        }
    }
}
