<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Définir le planning de commandes de l'application.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        
        // Exemple : Nettoyage des anciennes sessions
        // $schedule->command('auth:clear-resets')->everyFifteenMinutes();
        
        // Exemple : Backup de la base de données
        // $schedule->command('backup:run')->daily();
    }

    /**
     * Enregistrer les commandes pour l'application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}