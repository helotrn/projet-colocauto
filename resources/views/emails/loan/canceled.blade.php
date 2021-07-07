@extends('emails.layouts.main')

@section('content')
<p>
    Bonjour {{ $receiver->name }},
</p>

<p>
    {{ $sender->name }} a annulé l'emprunt à votre {{ $loan->loanable->name }}
    à partir de {{ $loan->departure_at }} et pour une durée de {{ $loan->duration_in_minutes }} minutes.
</p>

<p style="text-align: center;">
<a href="{{ url('/loans/' . $loan->id) }}" style="display: inline-block; background-color: #246AEA; padding: 10px; border-radius: 3px; color: white; font-weight: bold; text-decoration: none;" target="_blank">Voir l'emprunt</a>
</p>

<p style="text-align: right;">
    <em>- L'équipe LocoMotion</em>
</p>
@endsection
