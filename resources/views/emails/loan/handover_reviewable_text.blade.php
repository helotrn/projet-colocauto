@extends('emails.layouts.main_text')

@section('content')
{{ $caller->name }} a contesté les données du retour sur son emprunt
du {{ $loan->loanable->name }}
@if ($loan->loanable->owner)
    appartenant à {{ $loan->loanable->owner->user->name }}.
@else
    appartenant à la communauté.
@endif

@if (!!$handover->comments_on_contestation)
"
{{ $handover->comments_on_contestation }}
"
@endif

Voir l'emprunt [{{ env('FRONTEND_URL') . '/loans/'. $loan->id }}]
@endsection
