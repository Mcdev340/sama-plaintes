<?php

namespace App\Http\Controllers;

use App\Models\PieceJointe;
use App\Models\Plainte;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PieceJointeController extends Controller
{
    public function download(Plainte $plainte, PieceJointe $piece): StreamedResponse
    {
        $this->authorize('view', $plainte);
        abort_unless($piece->plainte_id === $plainte->id, 404);
        abort_unless(Storage::disk('local')->exists($piece->chemin), 404);

        return Storage::disk('local')->download($piece->chemin, $piece->nom_fichier);
    }
}
