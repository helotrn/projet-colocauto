@extends('emails.layouts.main_text')

@section('content')
{{ $caller->name }} a contesté les données de la prise de possession sur son emprunt
du {{ $loan->loanable->name }}
@if ($loan->loanable->owner)
    appartenant à {{ $loan->loanable->owner->user->name }}.
@else
    appartenant à la communauté.
@endif

@if (!!$takeover->comments_on_contestation)
"
{{ $takeover->comments_on_contestation }}
"
@endif

Voir l'emprunt [{{ url('/loans/'. $loan->id) }}]
@endsection
