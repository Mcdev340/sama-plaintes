<?php

namespace App\Support;

class MessagesValidation
{
    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'required' => 'Le champ :attribute est obligatoire.',
            'email' => 'L’adresse e-mail n’est pas valide.',
            'unique' => 'Cette valeur est déjà utilisée.',
            'confirmed' => 'La confirmation ne correspond pas.',
            'min.string' => 'Le champ :attribute doit contenir au moins :min caractères.',
            'max.string' => 'Le champ :attribute ne peut pas dépasser :max caractères.',
            'max.array' => 'Vous ne pouvez pas joindre plus de :max fichiers.',
            'max.file' => 'Chaque fichier doit faire moins de 5 Mo.',
            'mimes' => 'Formats acceptés : JPG, PNG, WEBP ou PDF.',
            'integer' => 'Le champ :attribute doit être un nombre entier.',
            'numeric' => 'Le champ :attribute doit être un nombre.',
            'between' => 'Le champ :attribute est hors limites.',
            'in' => 'La valeur sélectionnée est invalide.',
            'exists' => 'La sélection est introuvable.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function attributes(): array
    {
        return [
            'nom' => 'nom',
            'prenom' => 'prénom',
            'email' => 'e-mail',
            'telephone' => 'téléphone',
            'password' => 'mot de passe',
            'adresse' => 'adresse',
            'date_naissance' => 'date de naissance',
            'numero_identite' => 'numéro d’identité',
            'titre' => 'titre',
            'description' => 'description',
            'domaine' => 'domaine',
            'priorite' => 'priorité',
            'region' => 'région',
            'commune' => 'commune',
            'localisation' => 'localisation',
            'commentaire' => 'commentaire',
            'motif' => 'motif',
            'complement' => 'complément',
            'corps' => 'message',
            'sujet' => 'sujet',
            'message' => 'message',
            'note' => 'note',
            'delai_estime' => 'délai estimé',
            'autorite_id' => 'autorité',
            'code' => 'code d’activation',
        ];
    }
}
