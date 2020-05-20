@extends('emails.layouts.main')

@section('content')
<p>
    Bonjour {{ $borrower->user->name }},
</p>

<p>
    {{ $owner->user->name }} a accepté votre demande d'emprunt de {{ $loan->loanable->name }}
    à partir de {{ $loan->departure_at }}.
</p>

<p>
    {{ $intention->message_for_borrower }}
</p>

<p style="text-align: center;">
<a href="{{ url('/loans/' . $loan->id) }}" style="display: inline-block; background-color: #246AEA; padding: 10px; border-radius: 3px; color: white; font-weight: bold; text-decoration: none;" target="_blank">Voir l'emprunt</a>
</p>

<p style="text-align: right;">
    <em>- L'équipe LocoMotion</em>
</p>
@endsection
