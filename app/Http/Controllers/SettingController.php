<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class SettingController extends Controller
{

    public function index()
    {
        $settings = Setting::all();
        $ret = [];

        foreach ($settings as $se) {
            $ret[] = [
                'key' => $se->key,
                'value' => is_numeric($se->value) ? (float) $se->value : $se->value
            ];
        }

        return response()->json($ret, 200);
    }

    public function update(Request $request, Setting $setting)
    {
        if (Setting::where('key', '=', $request->key)->exists()) {
            $count = Setting::where('key', $request->key)->update(['value' => $request->value ? $request->value : '']);
        } else {
            $count = Setting::create($request->all());
        }

        return response()->json($count, 200);
    }

    public function getTimezones()
    {
        return response()->json(config('constants.TIMEZONES'), 200);
    }

    public function getLanguages()
    {
        $languages = config('constants.LANGUAGES');
        return $languages;
    }
}
