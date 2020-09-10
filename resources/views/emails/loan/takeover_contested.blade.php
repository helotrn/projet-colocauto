@extends('emails.layouts.main')

@section('content')
<p>
    Bonjour {{ $receiver->name }},
</p>

<p>
    {{ $caller->name }} a contesté les données entrées lors de la prise de possession du
    véhicule sur son emprunt de votre {{ $loan->loanable->name }} qui commençait
    à {{ $loan->departure_at }}.
</p>

<p>
    {{ $takeover->comments_on_contestation }}
</p>

<p>
    Un membre de l'équipe LocoMotion a été notifié et sera appelé à arbitrer la résolution du
    problème.
</p>

<p style="text-align: center;">
<a href="{{ url('/loans/' . $loan->id) }}" style="display: inline-block; background-color: #246AEA; padding: 10px; border-radius: 3px; color: white; font-weight: bold; text-decoration: none;" target="_blank">Voir l'emprunt</a>
</p>

<p style="text-align: right;">
    <em>- L'équipe LocoMotion</em>
</p>
@endsection
