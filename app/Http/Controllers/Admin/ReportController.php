<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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

    public function index(Request $request)
    {
        $query = $this->getBaseOrder()->orderBy('id', 'desc');

        if (!empty($q = $request->get('q'))) {
            $columns = Schema::getColumnListing('orders');
            $query->where(function (Builder $query) use ($q, $columns) {
                foreach ($columns as $column) {
                    $search = $q;
                    if (in_array(DB::getSchemaBuilder()->getColumnType('orders', $column), ['date', 'datetime'])) {
                        try {
                            $search = Carbon::parse($q)->format('Y-m-d');
                        } catch (InvalidFormatException $e) {
                        }
                    }
                    $query->orWhere($column, 'LIKE', '%' . trim($search) . '%');
                }
            });
        }

        return response()->json($query->paginate());
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
