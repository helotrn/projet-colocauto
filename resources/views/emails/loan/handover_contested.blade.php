@extends('emails.layouts.main')

@section('content')
<p style="text-align: justify; margin-top: 0; font-weight: 390; font-size: 17px; line-height: 24px; color: #343A40;">
    Bonjour {{ $receiver->name }},
</p>

<p style="text-align: justify; margin-top: 0; font-weight: 390; font-size: 17px; line-height: 24px; color: #343A40;">
    {{ $caller->name }} a contesté les données entrées lors du retour du
    véhicule sur son emprunt de votre {{ $loan->loanable->name }} qui commençait
    à {{ $loan->departure_at }}.
</p>

@if (!!$handover->comments_on_contestation)
<p style="text-align: justify; margin-top: 0; font-weight: 390; font-size: 17px; line-height: 24px; color: #343A40;">
    Commentaires:
</p>
<p style="text-align: justify; margin-top: 0; font-weight: 390; font-size: 17px; line-height: 24px; color: #343A40;">
    {{ $handover->comments_on_contestation }}
</p>
@endif

<p style="text-align: justify; margin-top: 0; font-weight: 390; font-size: 17px; line-height: 24px; color: #343A40;">
    Un membre de l'équipe LocoMotion a été notifié et sera appelé à arbitrer la résolution du
    problème.
</p>

<p style="text-align: center; margin: 32px auto 0 auto;">
    <a href="{{ url('/loans/' . $loan->id) }}" style="display: inline-block; background-color: #246AEA; padding: 8px 16px; border-radius: 5px; color: white; font-weight: bold; font-size: 17px; line-height: 24px; text-decoration: none;" target="_blank">Voir l'emprunt</a>
</p>

@endsection
