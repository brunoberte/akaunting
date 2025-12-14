<?php

namespace App\Settings;

use App\Models\Setting;

class SettingHelper
{
    public static function get($key = null, $default = null)
    {
		return Setting::query()->where('key', $key)->first()?->value ?? $default;
//        $setting = app('setting');
//
//        if (is_null($key)) {
//            return $setting;
//        }
//
//        if (is_array($key)) {
//            $setting->set($key);
//
//            return $setting;
//        }
//
//        return $setting->get($key, $default);
    }
}
