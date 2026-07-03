<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected static function booted()
    {
        static::saved(function ($user) {
            if ($user->isDirty('address') && $user->address) {
                // Parse address components
                $parts = array_map('trim', explode(',', $user->address));
                $parts = array_filter($parts);
                $count = count($parts);
                
                if ($count >= 4) {
                    $province = $parts[$count - 1];
                    $district = $parts[$count - 2];
                    $ward = $parts[$count - 3];
                    $detail = implode(', ', array_slice($parts, 0, $count - 3));
                } elseif ($count === 3) {
                    $province = $parts[2];
                    $district = $parts[1];
                    $ward = $parts[0];
                    $detail = $parts[0];
                } elseif ($count === 2) {
                    $province = $parts[1];
                    $district = $parts[0];
                    $ward = $parts[0];
                    $detail = $parts[0];
                } elseif ($count === 1) {
                    $province = $parts[0];
                    $district = $parts[0];
                    $ward = $parts[0];
                    $detail = $parts[0];
                } else {
                    $province = 'Chưa xác định';
                    $district = 'Chưa xác định';
                    $ward = 'Chưa xác định';
                    $detail = 'Chưa xác định';
                }

                $province = $province ?: 'Chưa xác định';
                $district = $district ?: 'Chưa xác định';
                $ward = $ward ?: 'Chưa xác định';
                $detail = $detail ?: 'Chưa xác định';

                // Look for an existing address for this user that matches the updated address string
                $normalizedNew = strtolower(trim($user->address));
                $matchedAddress = null;

                // Load fresh addresses relation to make sure we don't use stale cache
                $existingAddresses = \App\Models\UserAddress::where('user_id', $user->id)->get();
                foreach ($existingAddresses as $existingAddr) {
                    if (strtolower(trim($existingAddr->full_address)) === $normalizedNew) {
                        $matchedAddress = $existingAddr;
                        break;
                    }
                }

                // Unset default status for all user's existing addresses
                \App\Models\UserAddress::where('user_id', $user->id)->update(['is_default' => false]);

                if ($matchedAddress) {
                    // Update the matched address
                    $matchedAddress->update([
                        'name' => $user->name,
                        'phone' => $user->phone ?? $matchedAddress->phone ?? '0900000000',
                        'is_default' => true,
                    ]);
                } else {
                    // Create a new default address
                    \App\Models\UserAddress::create([
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'phone' => $user->phone ?? '0900000000',
                        'province' => $province,
                        'district' => $district,
                        'ward' => $ward,
                        'detail' => $detail,
                        'is_default' => true,
                    ]);
                }
            } elseif (($user->isDirty('name') || $user->isDirty('phone')) && ($user->name || $user->phone)) {
                // Sync name or phone changes to default address
                $defaultAddress = \App\Models\UserAddress::where('user_id', $user->id)
                    ->where('is_default', true)
                    ->first();
                if ($defaultAddress) {
                    $updateData = [];
                    if ($user->isDirty('name') && $user->name) {
                        $updateData['name'] = $user->name;
                    }
                    if ($user->isDirty('phone') && $user->phone) {
                        $updateData['phone'] = $user->phone;
                    }
                    if (!empty($updateData)) {
                        $defaultAddress->update($updateData);
                    }
                }
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'password',
        'is_admin',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    public function wishlist()
    {
        return $this->belongsToMany(Product::class, 'wishlists')->withTimestamps();
    }

    public function chatbotMessages()
    {
        return $this->hasMany(ChatbotMessage::class);
    }
}
