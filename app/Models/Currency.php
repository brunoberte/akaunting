<?php

namespace App\Models;

use Carbon\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string $code
 * @property float $rate
 * @property bool $enabled
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 * @property string $precision
 * @property string $symbol
 * @property string $symbol_first
 * @property string $decimal_mark
 * @property string $thousands_separator
 */
class Currency extends AppModel
{
	protected $table = 'currencies';

	protected $fillable = ['company_id', 'name', 'code', 'rate', 'enabled', 'precision', 'symbol', 'symbol_first', 'decimal_mark', 'thousands_separator'];

	public $sortable = ['name', 'code', 'rate', 'enabled'];

	public function accounts()
	{
		return $this->hasMany('App\Models\Account', 'currency_code', 'code');
	}

	public function customers()
	{
		return $this->hasMany('App\Models\Customer', 'currency_code', 'code');
	}

	public function revenues()
	{
		return $this->hasMany('App\Models\Revenue', 'currency_code', 'code');
	}

	public function payments()
	{
		return $this->hasMany('App\Models\Payment', 'currency_code', 'code');
	}

	/**
	 * Get the current precision.
	 *
	 * @return string
	 */
	public function getPrecisionAttribute($value)
	{
		if (is_null($value)) {
			return config('money.' . $this->code . '.precision');
		}

		return $value;
	}

	/**
	 * Get the current symbol.
	 *
	 * @return string
	 */
	public function getSymbolAttribute($value)
	{
		if (is_null($value)) {
			return config('money.' . $this->code . '.symbol');
		}

		return $value;
	}

	/**
	 * Get the current symbol_first.
	 *
	 * @return string
	 */
	public function getSymbolFirstAttribute($value)
	{
		if (is_null($value)) {
			return config('money.' . $this->code . '.symbol_first');
		}

		return $value;
	}

	/**
	 * Get the current decimal_mark.
	 *
	 * @return string
	 */
	public function getDecimalMarkAttribute($value)
	{
		if (is_null($value)) {
			return config('money.' . $this->code . '.decimal_mark');
		}

		return $value;
	}

	/**
	 * Get the current thousands_separator.
	 *
	 * @return string
	 */
	public function getThousandsSeparatorAttribute($value)
	{
		if (is_null($value)) {
			return config('money.' . $this->code . '.thousands_separator');
		}

		return $value;
	}
}
