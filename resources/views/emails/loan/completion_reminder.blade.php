@extends('emails.layouts.main') @section('content')
<p
    style="
        text-align: center;
        margin-top: 0;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
        margin-bottom: 32px;
    "
>
    Bonjour {{ $user->name }},
</p>

<p
    style="
        text-align: center;
        margin-top: 0;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    Votre réservation de {{ $loan->loanable->name }} du {{ \Carbon\Carbon::parse($loan->departure_at)->isoFormat('LL') }}
    n'a pas été complétée.
</p>

<p
    style="
        text-align: center;
        margin-top: 0;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    Dans 24 heures, sans action de votre part, l'emprunt sera clôturé avec le
    kilométrage estimé.
</p>

<p
    style="
        text-align: center;
        margin-top: 0;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    Merci de renseigner le kilométrage de départ et d'arrivée, ainsi qu'une
    éventuelle dépense de carburant.
</p>

<p
    style="
        text-align: center;
        margin-top: 0;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    @include('emails.partials.button', [
        'url' => env('FRONTEND_URL'). '/loans/' . $loan->id,
        'text' => 'Voir l\'emprunt'
    ])
</p>

@endsection
