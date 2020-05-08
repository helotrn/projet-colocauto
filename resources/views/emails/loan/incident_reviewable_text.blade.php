@extends('emails.layouts.main')

@section('content')
{{ $borrower->user->name }} a rapporté un incident lors de son emprunt du {{ $loan->loanable->name }} appartenant à {{ $owner->user->name }}.

{{ $incident->comments_on_incident }}

Voir l'emprunt [https://locomotion.app/loans/{{ $loan->id }}]
@endsection
