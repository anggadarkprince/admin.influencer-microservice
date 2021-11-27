<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = ['id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'is_influencer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->hasOneThrough(Role::class, UserRole::class, 'user_id', 'id', 'id', 'role_id');
    }

    public function permissions()
    {
        return optional(optional($this->role)->permissions)->pluck('name');
    }

    public function hasAccess($access)
    {
        return $this->permissions()->contains($access);
    }

    public function isAdmin(): bool
    {
        return $this->is_influencer == 0;
    }

    public function isInfluencer(): bool
    {
        return $this->is_influencer == 1;
    }

    public function getRevenueAttribute()
    {
        $orders = Order::where('user_id', $this->id)->where('complete', 1)->get();

        return $orders->sum(function(Order $order) {
            return $order->influencer_total;
        });
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
