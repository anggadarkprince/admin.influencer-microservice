<?php

namespace App\Http\Controllers\Checkout;

use App\Http\Controllers\Controller;
use App\Http\Resources\LinkResource;
use App\Models\Link;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    public function show($code)
    {
        $link = Link::where('code', $code)->first();

        return new LinkResource($link);
    }
}
