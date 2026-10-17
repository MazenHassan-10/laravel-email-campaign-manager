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
                    ->visible(fn (Campaign $record) => ! in_array($record->status , ['in_progress', 'completed', 'failed']))
                    ->action(function (Campaign $record) {

                        $record->update(['status' => 'in_progress']);

                        $record->matchingLeadsQuery()
                            ->select('leads.id')
                            ->chunkById(500, function ($leads) use ($record) {
                                    DB::table('campaign_leads')->insertOrIgnore(
                                        $leads->map(fn ($lead) =>[
                                            'campaign_id' => $record->id,
                                            'lead_id' => $lead->id,
                                            'status' => 'pending',
                                            'attempts' => 0,
                                            'created_at' => now(),
                                            'updated_at' => now(),
                                        ])->all()
                                    );
                                });

                            DB::table('campaign_leads')
                                ->where('campaign_id' , $record->id)
                                ->where('status', 'pending')
                                ->orderBy('lead_id')
                                ->chunkById(500 , function ($rows) use ($record) {
                                    foreach($rows as $row)
                                    {
                                        SendCampaignEmailJob::dispatch($record->id , $row->lead_id);
                                    }
                                });

                            $record->refreshStatus();

                        })
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
