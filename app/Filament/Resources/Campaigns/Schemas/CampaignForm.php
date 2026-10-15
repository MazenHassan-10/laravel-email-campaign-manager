<?php

namespace App\Filament\Resources\Campaigns\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;


class CampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('subject')->label('Subject')->required()->maxLength(255),

                TextInput::make('body')->label('Body')->required(),

                Select::make('status')->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'complete' => 'Complete',
                        'in_progress' => 'In_progress',
                    ])
                    ->required(),

                Section::make('Lead Filters')
                    ->statePath('audience_filters')
                    ->schema([
                        TextInput::make('filter_name')->label('Name'),

                        TextInput::make('filter_email')->label('Email'),

                        Select::make('filter_country')->label('Country')
                            ->options( fn ()=> self::getLeadOptions('country') ),

                        Select::make('filter_status')->label('Status')
                            ->options( fn ()=> self::getLeadOptions('status') ),

                        TextInput::make('filter_age_min')
                            ->label('Minimum Age')
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),

                        TextInput::make('filter_age_max')
                            ->label('Maximum Age')
                            ->numeric()
                            ->minValue(0)
                            ->nullable(),
                    ])->columns(2)
            ]);
    }
    private static function getLeadOptions($column): array
    {
        return \App\Models\Lead::query()
            ->whereNotNull($column)
            ->where($column , '!=' , '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column , $column)
            ->toArray();
    }
}

