<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

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
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function getEnabledCompaniesList()
    {
        return DB::table('companies')
            ->select('companies.id', 'settings.value as name')
            ->join('user_companies', 'companies.id', '=', 'user_companies.company_id')
            ->join('settings', 'settings.company_id', '=', 'companies.id')
            ->where('user_companies.user_id', $this->id)
            ->where('settings.key', 'general.company_name')
            ->where('companies.enabled', true)
            ->get();
//        return $this->morphToMany(Company::class, 'user', 'user_companies', 'user_id', 'company_id');
    }

    public function getDefaultCompany(): int
    {
        $enabledCompanies = $this->getEnabledCompaniesList();
        if ($enabledCompanies->count() == 0) {
            return 999; // FIXME
        }
        return $enabledCompanies[0]->id;
    }
}
