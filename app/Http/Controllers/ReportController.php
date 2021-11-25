<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private function getBaseOrder()
    {
        return DB::table('orders')->select([
            'transaction_id',
            'code',
            DB::raw('CONCAT(first_name, " ", last_name) AS name'),
            'email',
            'influencer_email',
            'address',
            'address2',
            'city',
            'country',
            'zip',
            'complete',
            'created_at',
        ]);
    }

    public function index()
    {
        $orders = $this->getBaseOrder()->orderBy('id', 'desc')->paginate();

        return response()->json($orders);
    }

    public function currentMonth()
    {
        $orders = $this->getBaseOrder()
            ->where('orders.created_at', '>=', date('Y-m-1'))
            ->where('orders.created_at', '<=', date('Y-m-t'))
            ->orderBy('id', 'desc')
            ->paginate();
        return response()->json($orders);
    }

    public function lastQuarter()
    {
        $orders = $this->getBaseOrder()
            ->where('orders.created_at', '>=', Carbon::now()->subMonths(4))
            ->where('orders.created_at', '<=', date('Y-m-t'))
            ->orderBy('id', 'desc')
            ->paginate();
        return response()->json($orders);
    }
}
