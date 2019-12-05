<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    public function up() {
        /*
        Utilisateur​ : Un identifiant de connexion, potentiellement lié à Google et fait aussi office de profil pour la personne
        */
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('lastname');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password'); //Obligatoire si Google ID est absent
            $table->string('googleid'); //Obligatoire si mot de passe est absent
            $table->longText('description')->nullable();
            $table->date('date_of_birth');
            $table->mediumText('address');
            $table->string('postal_code');
            $table->string('phone');
            $table->boolean('is_smart_phone');
            $table->string('other_phone');
            // Solde du compte Locomotion : Obligatoire
            $table->date('approval_date');// Approbation : Date d’approbation
            // Avatar (​Fichier​), optionnel
            // Emprunteur (​Emprunteur​), optionnel
            // Propriétaire (​Propriétaire​), optionnel
            // Fichiers (​Fichiers​), optionnel
            // Permis de conduire
            // Dossier de conduite de la SAAQ
            // Rapport de sinistre de la GAA
            // Communautés (​Communautés​), obligatoire
            // Actions (​Action​) : Les actions apportées à un E​ mprunt​, pas nécessairement les siens en ce qui concerne les Modérateurs
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('users');
    }
}
