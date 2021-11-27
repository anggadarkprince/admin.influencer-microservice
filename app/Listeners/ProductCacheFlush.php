<?php

namespace App\Listeners;

use App\Events\OrderCompletedEvent;
use App\Events\ProductUpdatedEvent;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class ProductCacheFlush
{
    /**
     * Handle the event.
     *
     * @param ProductUpdatedEvent $event
     * @return void
     */
    public function handle(ProductUpdatedEvent $event)
    {
        Cache::forget('products');
    }
}
