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
    @if ($isOwner == 1)
    L'emprunt de {{ $loan->loanable->name }} de {{ $duration }} par {{ $loan->borrower->user->name }} le {{ \Carbon\Carbon::parse($loan->departure_at)->isoFormat('LL') }} s'est conclu avec succès! 
    @else
    Votre emprunt de {{ $loan->loanable->name }} de {{ $duration }} le {{ \Carbon\Carbon::parse($loan->departure_at)->isoFormat('LL') }} s'est conclu avec succès! 
    @endif
</p>

<p style="text-align: center; margin-top: 32px">
    @include('emails.partials.button', [
        'url' => env('FRONTEND_URL') . '/loans/' . $loan->id,
        'text' => 'Voir l\'emprunt'
    ])
</p>

@endsection
