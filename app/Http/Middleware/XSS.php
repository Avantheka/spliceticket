<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Utility;

class XSS
{
     use \RachidLaasri\LaravelInstaller\Helpers\MigrationsHelper;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if(!file_exists(storage_path(). "/installed"))
        {
            return redirect()->route('LaravelUpdater::welcome');
        }
        if(Auth::check())
        {
            \App::setLocale(Auth::user()->lang);

            if(\Auth::user()->type == 'Super Admin')
            {

                $migrations             = $this->getMigrations();
                $dbMigrations           = $this->getExecutedMigrations();
                $Modulemigrations = glob(base_path().'/Modules/LandingPage/Database'.DIRECTORY_SEPARATOR.'Migrations'.DIRECTORY_SEPARATOR.'*.php');
                $numberOfUpdatesPending = (count($migrations) + count($Modulemigrations)) - count($dbMigrations);
                if($numberOfUpdatesPending > 0)
                {
                    // run code like seeder only when new migration
                    Utility::addNewData();
                    Utility::updateUserDefaultEmailTempData();
                    return redirect()->route('LaravelUpdater::welcome');
                }

            }

        }

        $input = $request->all();

        $request->merge($input);
        return $next($request);
    }
}
