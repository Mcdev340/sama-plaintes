<?php

namespace App\Http\Controllers;

use App\Enums\Domaine;
use App\Enums\Priorite;
use App\Enums\Role;
use App\Enums\StatutPlainte;
use App\Mail\MessageContact;
use App\Models\Plainte;
use App\Support\MessagesValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $reference = strtoupper(trim((string) $request->query('reference', '')));
        $suivie = null;

        if ($reference !== '') {
            $suivie = Plainte::query()->where('reference', $reference)->first();
        }

        $comptes = Plainte::query()
            ->selectRaw('domaine, count(*) as total')
            ->groupBy('domaine')
            ->pluck('total', 'domaine');

        return view('home', [
            'total' => Plainte::query()->count(),
            'resolues' => Plainte::query()->where(function ($query): void {
                $query->where('statut', StatutPlainte::Resolue->value)
                    ->orWhereHas('traitements', fn ($traitements) => $traitements->where('action', 'resolution'));
            })->count(),
            'enCours' => Plainte::query()->whereNotIn('statut', StatutPlainte::clos())->count(),
            'semaine' => Plainte::query()->where('date_soumission', '>=', now()->startOfWeek())->count(),
            'categories' => collect(Domaine::cases())->map(fn (Domaine $domaine): array => [
                'label' => $domaine->label(),
                'total' => (int) ($comptes[$domaine->value] ?? 0),
            ]),
            'reference' => $reference,
            'suivie' => $suivie,
        ]);
    }

    public function fonctionnalites(): View
    {
        return view('pages.fonctionnalites', [
            'domaines' => Domaine::cases(),
            'statuts' => StatutPlainte::cases(),
            'priorites' => Priorite::cases(),
        ]);
    }

    public function aPropos(): View
    {
        return view('pages.a-propos');
    }

    public function contact(): View
    {
        return view('pages.contact', [
            'sujets' => self::sujetsContact(),
        ]);
    }

    /**
     * @return list<string>
     */
    private static function sujetsContact(): array
    {
        return [
            'Demande d\'assistance',
            'Question',
            'Suggestion',
            'Autre',
        ];
    }

    public function envoyer(Request $request): RedirectResponse
    {
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'sujet' => ['required', Rule::in(self::sujetsContact())],
            'message' => ['required', 'string', 'min:20', 'max:2000'],
            'joindre_localisation' => ['nullable', 'boolean'],
            'latitude' => ['nullable', 'required_if:joindre_localisation,1', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_if:joindre_localisation,1', 'numeric', 'between:-180,180'],
            'lieu' => ['nullable', 'string', 'max:160'],
        ], MessagesValidation::messages(), MessagesValidation::attributes());

        $localisation = null;
        if ($request->boolean('joindre_localisation')) {
            $localisation = number_format((float) $donnees['latitude'], 6, '.', '').', '.number_format((float) $donnees['longitude'], 6, '.', '');
            if (! empty($donnees['lieu'])) {
                $localisation .= ' — '.$donnees['lieu'];
            }
        }

        Mail::to('contact@samaplaintes.sn')->send(new MessageContact(
            $donnees['nom'],
            $donnees['email'],
            $donnees['sujet'],
            $donnees['message'],
            $localisation,
        ));

        return back()->with('succes', 'Votre message a bien été envoyé. Une réponse vous sera adressée par e-mail.');
    }

    public function tableau(Request $request)
    {
        $role = $request->user()->role;

        return redirect()->route(match ($role) {
            Role::Citoyen => 'citoyen.dashboard',
            Role::Autorite => 'autorite.dashboard',
            Role::Superviseur => 'superviseur.dashboard',
        });
    }
}
