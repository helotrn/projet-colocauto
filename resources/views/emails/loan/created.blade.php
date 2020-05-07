@extends('emails.layouts.main')

@section('content')
<p>
    Bonjour {{ $owner->user->name }},
</p>

<p>
    {{ $borrower->user->name }} a demandé à emprunter votre {{ $loan->loanable->name }}
    à partir de {{ $loan->departure_at }} et pour un total de {{ $loan->duration_in_minutes }}.
</p>

<p>
    {{ $loan->message_for_owner }}
</p>

<p style="text-align: center;">
<a href="https://locomotion.app/loans/{{ $loan->id }}" style="display: inline-block; background-color: #246AEA; padding: 10px; border-radius: 3px; color: white; font-weight: bold; text-decoration: none;" target="_blank">Voir l'emprunt</a>
</p>

<p style="text-align: right;">
    <em>- L'équipe LocoMotion</em>
</p>
@endsection
