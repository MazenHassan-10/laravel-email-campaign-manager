<?php

namespace App\Jobs;

use App\Mail\CampaignMail;
use App\Models\Campaign;
use App\Models\Lead;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendCampaignEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public int $tries = 3;

    public function __construct(public int $CampaignID, public int $LeadID) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $campaign = Campaign::findOrFail($this->CampaignID);

        $lead = Lead::findOrFail($this->LeadID);

        $recipientData = DB::table('campaign_leads')
            ->where('campaign_id', $this->CampaignID)
            ->where('lead_id', $this->LeadID)->first();

        if (!$recipientData || $recipientData->status === 'sent') {
            return;
        }

        $claimed = DB::table('campaign_leads')
            ->where('campaign_id', $this->CampaignID)
            ->where('lead_id', $this->LeadID)
            ->where('status', 'pending')
            ->update([
                'status' => 'processing',
                'attempts' => DB::raw('attempts + 1'),
                'updated_at' => now()
            ]);

        if ($claimed !== 1) {
            return;
        }

        try {
            Mail::to($lead->email)->send(new CampaignMail($campaign));

            DB::table('campaign_leads')
            ->where('campaign_id', $this->CampaignID)
            ->where('lead_id', $this->LeadID)->where('status', 'processing')
            ->update([
                'status' => 'sent',
                'sent_at' => now(),
                'updated_at' => now()
            ]);

        } catch (\Throwable $th) {

            DB::table('campaign_leads')
            ->where('campaign_id', $this->CampaignID)
            ->where('lead_id', $this->LeadID)
            ->where('status', 'processing')
            ->update([
                'status' => 'pending',
                'updated_at' => now()
            ]);
            throw $th;

        }

    }

    public function failed(?\Throwable $exception) : void
    {
        DB::table('campaign_leads')
            ->where('campaign_id', $this->CampaignID)
            ->where('lead_id', $this->LeadID)
            ->update([
                'status' => 'failed',
                'updated_at' => now(),
            ]);
    }
}
