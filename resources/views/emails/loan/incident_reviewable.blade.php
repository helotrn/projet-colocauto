@extends('emails.layouts.main')

@section('content')
<p>
    {{ $borrower->user->name }} a rapporté un incident lors de son emprunt du {{ $loan->loanable->name }}
    @if ($owner)
        appartenant à {{ $owner->user->name }}.
    @else
        appartenant à la communauté.
    @endif
</p>

<p>
    {{ $incident->comments_on_incident }}
</p>

<p style="text-align: center;">
<a href="{{ url('/loans/'. $loan->id) }}" style="display: inline-block; background-color: #246AEA; padding: 10px; border-radius: 3px; color: white; font-weight: bold; text-decoration: none;" target="_blank">Voir l'emprunt</a>
</p>
@endsection
