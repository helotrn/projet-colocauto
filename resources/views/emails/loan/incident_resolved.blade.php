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
    Bonjour {{ $target->user->name }},
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
    L'incident rapporté lors de l'emprunt de votre {{ $loan->loanable->name }}
    qui commençait à {{ $loan->departure_at }} a été résolu.
</p>

<p style="text-align: center; margin: 32px auto 0 auto">
    @include('emails.partials.button', [
        'url' => env('FRONTEND_URL') . '/loans/' . $loan->id,
        'text' => 'Voir l\'emprunt'
    ])
</p>

@endsection
