@extends('emails.layouts.main')

@section('content')
<p style="text-align: justify; margin-top: 0; font-weight: 390; font-size: 17px; line-height: 24px; color: #343A40;">
    {{ $borrower->user->name }} a rapporté un incident lors de son emprunt du {{ $loan->loanable->name }}
    @if ($owner)
        appartenant à {{ $owner->user->name }}.
    @else
        appartenant à la communauté.
    @endif
</p>
@if (!!$incident->comments_on_incident)
<p style="text-align: justify; margin-top: 0; font-weight: 390; font-size: 17px; line-height: 24px; color: #343A40;">
    Commentaires:
</p>
<p style="text-align: justify; margin-top: 0; font-weight: 390; font-size: 17px; line-height: 24px; color: #343A40;">
    {{ $incident->comments_on_incident }}
</p>
@endif

<p style="text-align: center; margin: 32px auto 0 auto;">
    <a href="{{ url('/loans/'. $loan->id) }}" style="display: inline-block; background-color: #246AEA; padding: 8px 16px; border-radius: 5px; color: white; font-weight: bold; font-size: 17px; line-height: 24px; text-decoration: none;" target="_blank">Voir l'emprunt</a>
</p>

@endsection
