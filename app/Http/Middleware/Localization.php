<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //bots dont have HTTP_ACCEPT_LANGUAGE
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browserLanguage = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
        } else {
            $browserLanguage = 'missing';
        }

        $languages = config('constants.LANGUAGES');
        $defaultFrontendLanguage = config('constants.DEFAULT_FRONTEND_LANGUAGE');

        $languageFoundIndex = array_search($browserLanguage, array_column($languages, 'key'));

        //Returns array index if $browserLanguage is found in $languages, returns false otherwise. May return zero, strict comparison required
        if ($languageFoundIndex !== FALSE) {
            $currentLanguage = $languages[$languageFoundIndex]['key'];
        } else {
            $currentLanguage = $defaultFrontendLanguage;
        }

        App::setLocale($currentLanguage);

        return $next($request);
    }
}
