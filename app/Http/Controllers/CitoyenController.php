<?php

namespace App\Http\Controllers;

use App\Enums\Domaine;
use App\Enums\Priorite;
use App\Models\Plainte;
use App\Services\FiltrePlaintes;
use App\Services\PlainteService;
use App\Support\Catalogue;
use App\Support\MessagesValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CitoyenController extends Controller
{
    public function index(Request $request, FiltrePlaintes $filtres): View
    {
        $citoyen = $request->user()->citoyen;
        abort_unless($citoyen, 403);

        $plaintes = $filtres
            ->appliquer(Plainte::query()->where('citoyen_id', $citoyen->id), $request)
            ->latest('date_soumission')
            ->paginate(10)
            ->withQueryString();

        return view('citoyen.plaintes.index', compact('plaintes'));
    }

    public function create(): View
    {
        return view('citoyen.plaintes.create');
    }

    public function store(Request $request, PlainteService $plaintes): RedirectResponse
    {
        $donnees = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'domaine' => ['required', Rule::enum(Domaine::class)],
            'priorite' => ['required', Rule::enum(Priorite::class)],
            'region' => ['required', Rule::in(Catalogue::REGIONS)],
            'commune' => ['required', 'string', 'max:120'],
            'localisation' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'pieces' => ['nullable', 'array', 'max:4'],
            'pieces.*' => ['file', 'max:5120', 'mimes:jpg,jpeg,png,webp,pdf'],
        ], MessagesValidation::messages(), MessagesValidation::attributes());

        foreach (['domaine', 'priorite'] as $champ) {
            if ($donnees[$champ] instanceof \BackedEnum) {
                $donnees[$champ] = $donnees[$champ]->value;
            }
        }

        $plainte = $plaintes->deposer(
            $request->user(),
            $donnees,
            $request->file('pieces', []) ?? [],
        );

        return redirect()
            ->route('plaintes.show', $plainte)
            ->with('succes', 'Plainte '.$plainte->reference.' enregistrée.');
    }

    public function completer(Request $request, Plainte $plainte, PlainteService $plaintes): RedirectResponse
    {
        $this->authorize('completer', $plainte);

        $donnees = $request->validate([
            'complement' => ['required', 'string', 'min:5', 'max:2000'],
            'pieces' => ['nullable', 'array', 'max:4'],
            'pieces.*' => ['file', 'max:5120', 'mimes:jpg,jpeg,png,webp,pdf'],
        ], MessagesValidation::messages(), MessagesValidation::attributes());

        $plaintes->completer(
            $request->user(),
            $plainte,
            $donnees['complement'],
            $request->file('pieces', []) ?? [],
        );

        return back()->with('succes', 'Complément transmis.');
    }

    public function evaluer(Request $request, Plainte $plainte, PlainteService $plaintes): RedirectResponse
    {
        $this->authorize('completer', $plainte);

        $donnees = $request->validate([
            'note' => ['required', 'integer', 'between:1,5'],
            'commentaire' => ['nullable', 'string', 'max:1000'],
        ], MessagesValidation::messages(), MessagesValidation::attributes());

        $plaintes->evaluer($request->user(), $plainte, (int) $donnees['note'], $donnees['commentaire'] ?? null);

        return back()->with('succes', 'Merci, votre évaluation est enregistrée.');
    }
}
