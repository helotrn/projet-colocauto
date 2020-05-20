@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $borrower->user->name }},

{{ $owner->user->name }} a refusé la rallonge de l'emprunt de son {{ $loan->loanable->name }} qui commençait à {{ $loan->departure_at }}.

{{ $extension->message_for_borrower }}

Voir l'emprunt [{{ url('/loans/' . $loan->id) }}]

            - L'équipe LocoMotion
@endsection
