<?php

namespace App\Models;

use App\Settings\SettingHelper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property string $domain
 * @property bool $enabled
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
class Company extends AppModel
{
    use hasFactory;

    protected $table = 'companies';

    protected $dates = ['deleted_at'];

    protected $fillable = ['domain', 'enabled'];

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function currencies()
    {
        return $this->hasMany(Currency::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function recurring()
    {
        return $this->hasMany(Recurring::class);
    }

    public function revenues()
    {
        return $this->hasMany(Revenue::class);
    }

    public function settings()
    {
        return $this->hasMany(Setting::class);
    }

    public function transfers()
    {
        return $this->hasMany(Transfer::class);
    }

    public function users()
    {
        return $this->morphedByMany(User::class, 'user', 'user_companies', 'company_id', 'user_id');
    }

    public function vendors()
    {
        return $this->hasMany(Vendor::class);
    }

    public function setSettings()
    {
        $settings = $this->settings;

        foreach ($settings as $setting) {
            [$group, $key] = explode('.', $setting->getAttribute('key'));

            // Load only general settings
            if ($group != 'general') {
                continue;
            }

            $value = $setting->getAttribute('value');

            if (($key == 'company_logo') && empty($value)) {
                $value = 'public/img/company.png';
            }

            $this->setAttribute($key, $value);
        }

        // Set default default company logo if empty
        if ($this->getAttribute('company_logo') == '') {
            $this->setAttribute('company_logo', 'public/img/company.png');
        }
    }

    /**
     * Scope to get all rows filtered, sorted and paginated.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $sort
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCollect($query, $sort = 'name')
    {
        $request = request();

        $input = $request->input();
        $limit = $request->get('limit', SettingHelper::get('general.list_limit', '25'));

        return Auth::user()->companies()->filter($input)->sortable($sort)->paginate($limit);
    }

    /**
     * Scope to only include companies of a given enabled value.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEnabled($query, $value = 1)
    {
        return $query->where('enabled', $value);
    }

    /**
     * Sort by company name
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $direction
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function nameSortable($query, $direction)
    {
        return $query->join('settings', 'companies.id', '=', 'settings.company_id')
            ->where('key', 'general.company_name')
            ->orderBy('value', $direction)
            ->select('companies.*');
    }

    /**
     * Sort by company email
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $direction
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function emailSortable($query, $direction)
    {
        return $query->join('settings', 'companies.id', '=', 'settings.company_id')
            ->where('key', 'general.company_email')
            ->orderBy('value', $direction)
            ->select('companies.*');
    }

//    /**
//     * Get the current balance.
//     *
//     * @return string
//     */
//    public function getCompanyLogoAttribute()
//    {
//        return $this->getMedia('company_logo')->last();
//    }
}
