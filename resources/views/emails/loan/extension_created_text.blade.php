@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $owner->user->name }},

{{ $borrower->user->name }} a demandé à rallonger son emprunt de votre {{ $loan->loanable->name }} qui commençait à {{ $loan->departure_at }} et qui durerait maintenant {{ $extension->new_duration }} minutes.

{{ $extension->comments_on_extension }}

Voir l'emprunt [{{ url('/loans/' . $loan->id) }}]

            - L'équipe LocoMotion
@endsection
