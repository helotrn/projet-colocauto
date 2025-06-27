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
    Bonjour {{ $owner->user->name }},
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
    {{ $borrower->user->name }} a reservé votre véhicule {{
    $loan->loanable->name }} à partir du @datetime($loan->departure_at) et pour une
    durée de @duration($loan->duration_in_minutes).
</p>

<br />

@if (!!$loan->message_for_owner)
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
    Commentaires:
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
    {{ $loan->message_for_owner }}
</p>
@endif

<p style="text-align: center; margin: 32px auto 0 auto">
    @include('emails.partials.button', [
        'url' => env('FRONTEND_URL') . '/loans/' . $loan->id,
        'text' => 'Voir l\'emprunt'
    ])
</p>

@endsection
