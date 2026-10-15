<?php

namespace App\Filament\Resources\Leads\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Name')->required()->maxLength(255),

                TextInput::make('email')->label('Email')->required()->maxLength(255),

                Select::make('status')->label('Status')
                    ->options([
                        'new' => 'New',
                        'contacted' => 'Contacted',
                        'interested' => 'Interested',
                        'converted' => 'Converted',
                    ])
                    ->required(),

                Select::make('country')->label('Country')
                    ->options([
                        'Egypt' => 'Egypt',
                        'Saudi Arabia' => 'Saudi Arabia',
                        'UAE' => 'UAE',
                        'United States' => 'United States',
                    ])->required(),

                DatePicker::make('birthdate')->label('Birthdate')->required()
            ]);
    }
}
