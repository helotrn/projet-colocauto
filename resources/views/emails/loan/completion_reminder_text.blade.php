@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $user->name }},

Votre réservation de {{ $loan->loanable->name }} du {{ \Carbon\Carbon::parse($loan->departure_at)->isoFormat('LL') }} n'a pas été complétée.

Dans 24 heures, sans action de votre part, l'emprunt sera clôturé avec le kilométrage estimé.

Merci de renseigner le kilométrage de départ et d'arrivée, ainsi qu'une éventuelle dépense de carburant.

Voir l'emprunt [{{ env('FRONTEND_URL') . '/loans/' . $loan->id }}]

            - L'équipe Coloc'Auto
@endsection
