@extends('emails.layouts.main')

@section('content')
<p>
    Bonjour {{ $target->user->name }},
</p>

<p>
    L'incident rapporté lors de l'emprunt de votre {{ $loan->loanable->name }} qui commençait à {{ $loan->departure_at }} a été résolu.
</p>

<p style="text-align: center;">
<a href="{{ url('/loans/' . $loan->id) }}" style="display: inline-block; background-color: #246AEA; padding: 10px; border-radius: 3px; color: white; font-weight: bold; text-decoration: none;" target="_blank">Voir l'emprunt</a>
</p>

<p style="text-align: right;">
    <em>- L'équipe LocoMotion</em>
</p>
@endsection
