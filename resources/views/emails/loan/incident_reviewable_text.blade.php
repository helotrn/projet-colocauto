@extends('emails.layouts.main_text')

@section('content')
{{ $borrower->user->name }} a rapporté un incident lors de son emprunt du {{ $loan->loanable->name }} appartenant à {{ $owner->user->name }}.

{{ $incident->comments_on_incident }}

Voir l'emprunt [{{ url('/loans/'. $loan->id) }}]
@endsection
