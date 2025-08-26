<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'exportCount',
        'contact_permission',
        'photo_permission',
        'image_merge_permission',
        'add_view_permission',
        'role',
        'properties_share_list',
        'images_share_list',
        'properties_viewed',
        'last_login_at',
    ];

    public function activities()
    {
        return $this->hasMany(AgentActivity::class, 'user_id');
    }

    public function incrementExportCount($type)
    {
        // Allowed types
        $allowedTypes = ['excel', 'pdf', 'search'];
        if (!in_array($type, $allowedTypes)) {
            throw new \Exception("Invalid type provided: {$type}");
        }

        // Get current exportCount as array (casted automatically by Laravel)
        $exportCount = $this->exportCount ?? [];

        // Today's date
        $today = now()->toDateString();

        // If type not exists, initialize
        if (!isset($exportCount[$type])) {
            $exportCount[$type] = [
                'date'  => $today,
                'count' => 0,
            ];
        }

        // If old date → reset
        if ($exportCount[$type]['date'] !== $today) {
            $exportCount[$type] = [
                'date'  => $today,
                'count' => 0,
            ];
        }

        // Get daily limit from utilities table
        $dailyLimit = Utility::where('key', 'agent_limit_per_day')
            ->value('value') ?? 10;

        // Check limit
        if ($exportCount[$type]['count'] >= $dailyLimit) {
            return false;
        }

        // Increment
        $exportCount[$type]['count']++;

        $this->exportCount = $exportCount;
        $this->save();

        return $exportCount[$type]['count']; 
    }

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
        'exportCount'       => 'array',
    ];
}
