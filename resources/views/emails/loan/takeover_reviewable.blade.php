@extends('emails.layouts.main')

@section('content')
<p>
    {{ $caller->name }} a contesté les données de la prise de possession sur son emprunt
    du {{ $loan->loanable->name }}
    @if ($loan->loanable->owner)
        appartenant à {{ $loan->loanable->owner->user->name }}.
    @else
        appartenant à la communauté.
    @endif
</p>

@if (!!$takeover->comments_on_contestation)
<p>
    {{ $takeover->comments_on_contestation }}
</p>
@endif

<p style="text-align: center;">
<a href="{{ url('/loans/'. $loan->id) }}" style="display: inline-block; background-color: #246AEA; padding: 10px; border-radius: 3px; color: white; font-weight: bold; text-decoration: none;" target="_blank">Voir l'emprunt</a>
</p>
@endsection
