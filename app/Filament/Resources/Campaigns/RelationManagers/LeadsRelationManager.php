<?php

namespace App\Filament\Resources\Campaigns\RelationManagers;

use App\Filament\Resources\Campaigns\Pages\ViewCampaign;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeadsRelationManager extends RelationManager
{
    protected static string $relationship = 'leads';


    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->label('Lead')
                    ->searchable(),

                TextColumn::make('email')->searchable(),

                TextColumn::make('pivot.status')->label('Status'),

                TextColumn::make('pivot.attempts')->label('Attempts'),

                TextColumn::make('pivot.sent_at')->label('Sent at')->dateTime(),

            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'sent' => 'Sent',
                        'failed' => 'Failed',
                    ])
                    ->query(
                        fn(Builder $query, array $data)
                        => filled($data['value']) ? $query->where('campaign_leads.status', $data['value'])
                            : $query
                    ),
            ])->poll('5s');
    }
    public static function canViewForRecord(
        \Illuminate\Database\Eloquent\Model $ownerRecord,
        string $pageClass
    ): bool
    {
        return $pageClass === ViewCampaign::class;
    }
}
