@extends('emails.layouts.main')

@section('content')
<p>
    Bonjour {{ $borrower->user->name }},
</p>

<p>
    {{ $owner->user->name }} a accepté la rallonge de l'emprunt de son {{ $loan->loanable->name }} qui commençait à {{ $loan->departure_at }} et qui durera maintenant {{ $extension->new_duration }} minutes.
</p>

<p>
    {{ $extension->message_for_borrower }}
</p>

<p style="text-align: center;">
<a href="{{ url('/loans/' . $loan->id) }}" style="display: inline-block; background-color: #246AEA; padding: 10px; border-radius: 3px; color: white; font-weight: bold; text-decoration: none;" target="_blank">Voir l'emprunt</a>
</p>

<p style="text-align: right;">
    <em>- L'équipe LocoMotion</em>
</p>
@endsection
