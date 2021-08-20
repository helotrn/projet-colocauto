@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $borrower->user->name }},

{{ $owner->user->name }} a accepté la rallonge de l'emprunt de son {{ $loan->loanable->name }} qui commençait à {{ $loan->departure_at }} et qui durera maintenant {{ $extension->new_duration }} minutes.

{{ $extension->message_for_borrower }}

Voir l'emprunt [{{ env('FRONTEND_URL') . '/loans/' . $loan->id }}]

            - L'équipe LocoMotion
@endsection
