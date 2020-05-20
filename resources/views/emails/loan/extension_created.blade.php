@extends('emails.layouts.main')

@section('content')
<p>
    Bonjour {{ $owner->user->name }},
</p>

<p>
    {{ $borrower->user->name }} a demandé à rallonger son emprunt de votre {{ $loan->loanable->name }} qui commençait à {{ $loan->departure_at }} et qui durerait maintenant {{ $extension->new_duration }} minutes.
</p>

<p>
    {{ $extension->comments_on_extension }}
</p>

<p style="text-align: center;">
<a href="{{ url('/loans/' . $loan->id) }}" style="display: inline-block; background-color: #246AEA; padding: 10px; border-radius: 3px; color: white; font-weight: bold; text-decoration: none;" target="_blank">Voir l'emprunt</a>
</p>

<p style="text-align: right;">
    <em>- L'équipe LocoMotion</em>
</p>
@endsection

