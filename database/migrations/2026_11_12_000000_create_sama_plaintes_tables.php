<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citoyens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->string('prenom');
            $table->text('adresse')->nullable();
            $table->string('telephone', 20)->nullable();
            $table->date('date_naissance')->nullable();
            $table->string('numero_identite', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('autorites_locales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('nom');
            $table->string('zone');
            $table->string('region');
            $table->text('competences');
            $table->string('telephone_contact', 20)->nullable();
            $table->string('email_contact')->nullable();
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });

        Schema::create('superviseurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('matricule', 50)->unique();
            $table->string('departement');
            $table->unsignedTinyInteger('niveau_acces')->default(1);
            $table->timestamps();
        });

        Schema::create('plaintes', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('titre');
            $table->text('description');
            $table->string('domaine');
            $table->string('priorite');
            $table->string('statut')->index();
            $table->string('localisation');
            $table->string('region')->index();
            $table->string('commune');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignId('citoyen_id')->constrained('citoyens')->restrictOnDelete();
            $table->foreignId('autorite_id')->nullable()->constrained('autorites_locales')->nullOnDelete();
            $table->foreignId('autorite_suggeree_id')->nullable()->constrained('autorites_locales')->nullOnDelete();
            $table->timestamp('date_soumission')->index();
            $table->timestamp('date_echeance')->nullable()->index();
            $table->timestamp('date_cloture')->nullable();
            $table->text('motif_rejet')->nullable();
            $table->unsignedTinyInteger('niveau_escalade')->default(0);
            $table->unsignedTinyInteger('note_satisfaction')->nullable();
            $table->text('commentaire_satisfaction')->nullable();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plainte_id')->constrained('plaintes')->cascadeOnDelete();
            $table->string('nom_fichier');
            $table->string('chemin');
            $table->string('type_fichier', 100);
            $table->unsignedBigInteger('taille');
            $table->timestamp('date_upload');
            $table->timestamps();
        });

        Schema::create('assignations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plainte_id')->constrained('plaintes')->cascadeOnDelete();
            $table->foreignId('superviseur_id')->constrained('superviseurs')->restrictOnDelete();
            $table->foreignId('autorite_id')->constrained('autorites_locales')->restrictOnDelete();
            $table->timestamp('date_assignation');
            $table->unsignedInteger('delai_estime');
            $table->text('commentaire')->nullable();
            $table->timestamps();
        });

        Schema::create('traitements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plainte_id')->constrained('plaintes')->cascadeOnDelete();
            $table->foreignId('autorite_id')->nullable()->constrained('autorites_locales')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('responsable');
            $table->string('action');
            $table->text('commentaire')->nullable();
            $table->timestamp('date_traitement')->index();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destinataire_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plainte_id')->nullable()->constrained('plaintes')->cascadeOnDelete();
            $table->text('message');
            $table->string('type')->default('info');
            $table->boolean('lu')->default(false);
            $table->timestamp('date_envoi')->index();
            $table->timestamps();
            $table->index(['destinataire_id', 'lu']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plainte_id')->constrained('plaintes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('corps');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('traitements');
        Schema::dropIfExists('assignations');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('plaintes');
        Schema::dropIfExists('superviseurs');
        Schema::dropIfExists('autorites_locales');
        Schema::dropIfExists('citoyens');
    }
};
