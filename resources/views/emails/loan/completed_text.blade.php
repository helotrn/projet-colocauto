@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $user->name }},

@if ($isOwner == 1)
L'emprunt de {{ $loan->loanable->name }} de {{ $duration }} par {{ $loan->borrower->user->name }} le {{ \Carbon\Carbon::parse($loan->departure_at)->isoFormat('LL') }} s'est conclu avec succès! 
@else
Votre emprunt de {{ $loan->loanable->name }} de {{ $duration }} le {{ \Carbon\Carbon::parse($loan->departure_at)->isoFormat('LL') }} s'est conclu avec succès! 
@endif

Voir l'emprunt [{{ env('FRONTEND_URL') . '/loans/' . $loan->id }}]

        - L'équipe Coloc'Auto
@endsection
