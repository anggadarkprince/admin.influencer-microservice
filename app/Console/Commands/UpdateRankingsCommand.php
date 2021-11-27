<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class UpdateRankingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:rankings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update ranking data to cache';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $user = User::where('is_influencer', 1)->get();

        $user->each(function(User $user) {
            $orders = Order::where('user_id', $user->id)->where('complete', 1)->get();
            $revenue = $orders->sum(function(Order $order) {
                return number_format($order->influencer_total, 2);
            });

            Redis::zadd('rankings', $revenue, $user->full_name);
        });
    }
}
