<?php

namespace App\Http\Controllers;

use App\Models\PieceJointe;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function show(PieceJointe $document): StreamedResponse
    {
        $this->authorize('view', $document->plainte);
        abort_unless(Storage::disk('local')->exists($document->chemin), 404);

        return Storage::disk('local')->download($document->chemin, $document->nom_fichier);
    }
}
