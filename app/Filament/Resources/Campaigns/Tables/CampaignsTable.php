<?php

namespace App\Filament\Resources\Campaigns\Tables;

use App\Jobs\SendCampaignEmailJob;
use App\Models\Campaign;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class CampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')->searchable()->sortable(),
                TextColumn::make('body')->searchable()->sortable(),
                TextColumn::make('status')->searchable()->sortable(),
                TextColumn::make('leads_count')->label('Leads Count')
                    ->state(fn(Campaign $record) => $record->matchingLeadsQuery()->count()),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('send')->label('Send')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Campaign $record) {
                        $record->matchingLeadsQuery()
                            ->select('leads.id')
                            ->chunkById(500, function ($leads) use ($record) {
                                foreach ($leads as $lead) {
                                    $inserted = DB::table('campaign_leads')
                                        ->insertOrIgnore([
                                            'campaign_id' => $record->id,
                                            'lead_id' => $lead->id,
                                            'status' => 'pending',
                                            'attempts' => 0,
                                            'created_at' => now(),
                                            'updated_at' => now(),
                                        ]);
                                    if ($inserted === 1) {
                                        SendCampaignEmailJob::dispatch($record->id, $lead->id);
                                    }
                                }
                            });
                        $record->update([
                            'status' => 'in_progress',
                        ]);
                    })
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
