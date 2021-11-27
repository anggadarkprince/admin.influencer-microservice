<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use JsonSerializable;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|Arrayable|JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            $this->mergeWhen(Auth::check() && Auth::user()->isAdmin(),  [
                'role' => $this->role
            ]),
            $this->mergeWhen(Auth::check() && Auth::user()->isInfluencer(),  [
                'revenue' => $this->revenue
            ]),
        ];
    }
}
