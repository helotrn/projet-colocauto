@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $user->name }},

Votre réservation de {{ $loan->loanable->name }} du {{ \Carbon\Carbon::parse($loan->departure_at)->isoFormat('LL') }} n'a pas été complétée par vos soins.
La date de retour prévue est {{ \Carbon\Carbon::parse($loan->actual_return_at)->isoFormat('LL') }}.
Merci de l'annuler si vous n'avez finalement pas utilisé le véhicule ou de la compléter pour indiquer le kilométrage parcouru.

Compléter ou annuler la réservation : [{{ env('FRONTEND_URL') . '/loans/' . $loan->id }}]

Sans action de votre part dans les prochaines 24h, elle sera clôturée automatiquement avec les kilomètres estimés.

        - L'équipe Coloc'Auto
@endsection
