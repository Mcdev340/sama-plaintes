<?php

namespace App\Console\Commands;

use App\Services\PlainteService;
use Illuminate\Console\Command;

class EscaladerPlaintesCommand extends Command
{
    protected $signature = 'plaintes:escalader';

    protected $description = 'Escalade les plaintes dont le délai de traitement est dépassé';

    public function handle(PlainteService $plaintes): int
    {
        $nombre = $plaintes->escaladerLesRetards();
        $this->info($nombre.' plainte(s) escaladée(s).');

        return self::SUCCESS;
    }
}
