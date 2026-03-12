<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function savedSearches()
    {
        return $this->hasMany(SavedSearch::class);
    }

    public function followedCompanies()
    {
        return $this->belongsToMany(Company::class, 'company_follows')->withTimestamps();
    }

    public function recentlyViewedCompanies()
    {
        return $this->belongsToMany(Company::class, 'company_views')
            ->withPivot('viewed_at')
            ->orderByPivot('viewed_at', 'desc')
            ->distinct();
    }

    public function isFollowing(Company $company): bool
    {
        return $this->followedCompanies()->where('company_id', $company->id)->exists();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
