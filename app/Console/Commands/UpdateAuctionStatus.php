<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Auction;

class UpdateAuctionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auction:update-auction-status';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update-auction-status';


    public function handle()
    {

       \Log::info('⏰ Auction status job is running...');
        // تحديث حالة المزادات إلى "active" إذا تجاوز تاريخ البدء
        $auctionsToActivate = auction::where('status', 'pending')
                                     ->where('start_date', '<=', now())  // تاريخ البدء في الماضي أو الحاضر
                                     ->get();

        foreach ($auctionsToActivate as $auction) {
            $auction->status = 'active';
            $auction->save();
            $this->info('update statusfor' . $auction->id . ' to "active"');
        }

        // تحديث حالة المزادات إلى "expired" إذا تجاوز تاريخ الانتهاء
        $auctionsToExpire = auction::where('status', 'active')
                                   ->where('end_date', '<=', now())  // تاريخ الانتهاء في الماضي
                                   ->get();

        foreach ($auctionsToExpire as $auction) {
            $auction->status = 'expired';
            $auction->save();
            $this->info('update statusfor' . $auction->id . ' to "expired"');
        }
    }
}
