<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function dateFormatByLanguage($preferred_language, $format)
    {
        $languages = config('constants.LANGUAGES');
        $defaultFrontendLanguage = config('constants.DEFAULT_FRONTEND_LANGUAGE');
        if (isset($preferred_language) && !empty($preferred_language)) {
            $currentLanguage = $preferred_language;
        } else {
            //Middleware Localization already set locale to browser language or english
            $currentLanguage = app()->getLocale();
        }
        $languageFoundIndex = array_search($currentLanguage, array_column($languages, 'key'));
        $defaultLanguageFoundIndex = array_search($currentLanguage, array_column($languages, 'key'));
        if ($languageFoundIndex !== FALSE) {
            $dateFormat = $languages[$languageFoundIndex][$format];
        } else {
            $dateFormat = $languages[$defaultLanguageFoundIndex][$format];
        }
        return $dateFormat;
    }
}
