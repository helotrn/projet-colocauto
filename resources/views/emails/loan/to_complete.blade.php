@extends('emails.layouts.main') @section('content')
<p
    style="
        text-align: center;
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
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    Votre réservation de {{ $loan->loanable->name }} du {{ \Carbon\Carbon::parse($loan->departure_at)->isoFormat('LL') }} n'a pas été complétée par vos soins.
    La date de retour prévue est {{ \Carbon\Carbon::parse($loan->actual_return_at)->isoFormat('LL') }}.
    Merci de la compléter pour indiquer le kilométrage parcouru ou de l'annuler si vous n'avez finalement pas utilisé le véhicule.
</p>
<p style="text-align: center; margin-top: 32px">
    @include('emails.partials.button', [
        'url' => env('FRONTEND_URL') . '/loans/' . $loan->id,
        'text' => 'Compléter ou annuler la réservation'
    ])
</p>

<p
    style="
        text-align: center;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    Sans action de votre part dans les prochaines 24h, elle sera clôturée automatiquement avec les kilomètres estimés.
</p>


@endsection
