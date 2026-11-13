<?php

namespace App\Http\Controllers;

use App\Enums\Domaine;
use App\Enums\Priorite;
use App\Enums\Role;
use App\Enums\StatutPlainte;
use App\Models\AutoriteLocale;
use App\Models\Plainte;
use App\Models\Traitement;
use App\Models\User;
use App\Services\FiltrePlaintes;
use App\Services\PlainteService;
use App\Services\StatistiquesService;
use App\Support\Catalogue;
use App\Support\MessagesValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SuperviseurController extends Controller
{
    public function dashboard(Request $request, StatistiquesService $statistiques): View
    {
        abort_unless($request->user()->superviseur, 403);

        return view('superviseur.dashboard', [
            'stats' => $statistiques->synthese(Plainte::query()),
            'file' => Plainte::query()
                ->whereNull('autorite_id')
                ->whereNotIn('statut', StatutPlainte::clos())
                ->latest('date_soumission')
                ->limit(6)
                ->get(),
            'activite' => Traitement::query()->with('plainte')->latest('date_traitement')->limit(8)->get(),
        ]);
    }

    public function index(Request $request, FiltrePlaintes $filtres): View
    {
        abort_unless($request->user()->superviseur, 403);

        $plaintes = $filtres
            ->appliquer(Plainte::query(), $request)
            ->latest('date_soumission')
            ->paginate(12)
            ->withQueryString();

        return view('superviseur.plaintes.index', compact('plaintes'));
    }

    public function export(Request $request, FiltrePlaintes $filtres): StreamedResponse
    {
        abort_unless($request->user()->superviseur, 403);
        $plaintes = $filtres->appliquer(Plainte::query(), $request)->orderBy('date_soumission')->get();

        return response()->streamDownload(function () use ($plaintes): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['reference', 'date', 'region', 'commune', 'domaine', 'priorite', 'statut', 'echeance'], ';');
            foreach ($plaintes as $plainte) {
                fputcsv($out, [
                    $plainte->reference,
                    $plainte->date_soumission?->format('Y-m-d H:i'),
                    $plainte->region,
                    $plainte->commune,
                    $plainte->domaine->value,
                    $plainte->priorite->value,
                    $plainte->statut->value,
                    $plainte->date_echeance?->format('Y-m-d'),
                ], ';');
            }
            fclose($out);
        }, 'sama-plaintes.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function analyser(Request $request, Plainte $plainte, PlainteService $plaintes): RedirectResponse
    {
        $this->authorize('assigner', $plainte);
        $plaintes->analyser($request->user(), $plainte, $request->string('commentaire')->toString() ?: null);

        return back()->with('succes', 'Plainte prise en analyse.');
    }

    public function assigner(Request $request, Plainte $plainte, PlainteService $plaintes): RedirectResponse
    {
        $this->authorize('assigner', $plainte);
        $donnees = $request->validate([
            'autorite_id' => ['required', 'integer', 'exists:autorites_locales,id'],
            'delai' => ['required', 'integer', 'between:1,60'],
            'commentaire' => ['nullable', 'string', 'max:1000'],
            'priorite' => ['nullable', Rule::enum(Priorite::class)],
        ], MessagesValidation::messages(), MessagesValidation::attributes());

        $autorite = AutoriteLocale::query()->findOrFail($donnees['autorite_id']);
        $priorite = $donnees['priorite'] ?? null;
        $plaintes->assigner(
            $request->user(),
            $plainte,
            $autorite,
            (int) $donnees['delai'],
            $donnees['commentaire'] ?? null,
            $priorite instanceof \BackedEnum ? $priorite->value : $priorite,
        );

        return back()->with('succes', 'Plainte assignée à '.$autorite->nom.'.');
    }

    public function escalader(Request $request, Plainte $plainte, PlainteService $plaintes): RedirectResponse
    {
        $this->authorize('assigner', $plainte);
        $donnees = $request->validate([
            'commentaire' => ['nullable', 'string', 'max:1000'],
        ], MessagesValidation::messages(), MessagesValidation::attributes());
        $plaintes->escalader($request->user(), $plainte, $donnees['commentaire'] ?? null);

        return back()->with('succes', 'Plainte escaladée.');
    }

    public function clore(Request $request, Plainte $plainte, PlainteService $plaintes): RedirectResponse
    {
        $this->authorize('assigner', $plainte);
        $donnees = $request->validate([
            'commentaire' => ['nullable', 'string', 'max:1000'],
        ], MessagesValidation::messages(), MessagesValidation::attributes());
        $plaintes->cloturer($request->user(), $plainte, $donnees['commentaire'] ?? null);

        return back()->with('succes', 'Dossier clos.');
    }

    public function autorites(Request $request): View
    {
        abort_unless($request->user()->superviseur, 403);

        $autorites = AutoriteLocale::query()->with('user')->withCount('plaintes')->orderBy('region')->orderBy('nom')->get();

        return view('superviseur.autorites.index', compact('autorites'));
    }

    public function createAutorite(): View
    {
        return view('superviseur.autorites.create');
    }

    public function storeAutorite(Request $request): RedirectResponse
    {
        abort_unless($request->user()->superviseur, 403);

        $donnees = $request->validate([
            'prenom' => ['required', 'string', 'max:100'],
            'nom_agent' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telephone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
            'institution' => ['required', 'string', 'max:255'],
            'zone' => ['required', 'string', 'max:120'],
            'region' => ['required', Rule::in(Catalogue::REGIONS)],
            'email_contact' => ['required', 'email', 'max:255'],
            'competences' => ['required', 'array', 'min:1'],
            'competences.*' => [Rule::enum(Domaine::class)],
        ], MessagesValidation::messages(), MessagesValidation::attributes());

        $competences = array_map(
            fn ($competence) => $competence instanceof \BackedEnum ? $competence->value : $competence,
            $donnees['competences'],
        );

        $user = User::create([
            'name' => $donnees['prenom'].' '.$donnees['nom_agent'],
            'prenom' => $donnees['prenom'],
            'nom' => $donnees['nom_agent'],
            'email' => $donnees['email'],
            'telephone' => $donnees['telephone'],
            'adresse' => $donnees['zone'],
            'password' => $donnees['password'],
            'role' => Role::Autorite,
            'langue' => 'fr',
            'actif' => true,
            'email_verified_at' => now(),
        ]);

        AutoriteLocale::create([
            'user_id' => $user->id,
            'nom' => $donnees['institution'],
            'zone' => $donnees['zone'],
            'region' => $donnees['region'],
            'competences' => implode(',', $competences),
            'telephone_contact' => $donnees['telephone'],
            'email_contact' => $donnees['email_contact'],
            'actif' => true,
        ]);

        return redirect()->route('superviseur.autorites.index')->with('succes', 'Autorité créée.');
    }
}
