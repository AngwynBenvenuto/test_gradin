<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstalled
{
	public function handle(Request $request, Closure $next): Response
    {
        $envExists = file_exists(base_path('.env'));
        $installedExists = file_exists(storage_path('installed'));
        $isInstalled = $envExists && $installedExists;

        // Mendefinisikan rute yang termasuk dalam proses instalasi
        $isInstallRoute = $request->is('install') || $request->is('install/*');

        // Pengecualian POST Kritis (untuk AJAX instalasi)
        $isCriticalPostSetup = $request->isMethod('post') && 
                               ($request->is('install/process') || $request->is('install/migrate'));
        
        if ($request->isMethod('post') && $isCriticalPostSetup) {
            return $next($request);
        }

        //JIKA BELUM INSTALLED
        if (!$isInstalled) {
            // Jika request BUKAN ke rute instalasi (termasuk home, admin, dll.), redirect ke /install
            if (!$isInstallRoute) {
                return redirect()->to('/install');
            }
            // Jika request adalah ke /install, lanjutkan
            return $next($request);
        }

        //JIKA SUDAH INSTALLED
        if ($isInstalled) {
            // Pengecualian: Jika ini adalah POST request ke rute setup kritis,
            // izinkan untuk menyelesaikan respons JSON (walaupun seharusnya jarang terjadi).
            if ($isCriticalPostSetup) {
                return $next($request);
            }
            
            // Jika request adalah ke rute instalasi (GET /install), redirect ke home
            if ($isInstallRoute) {
               return redirect()->route('landing');
            }
            
            // Jika sudah terinstal dan request BUKAN /install, lanjutkan ke routing normal
            // (Ini akan mencakup / dan /admin)
            
        }

        
        return $next($request);
    }
}