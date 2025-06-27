@extends('emails.layouts.main') @section('content')
<p
    style="
        text-align: center;
        margin-top: 0;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
        margin-bottom: 32px;
    "
>
    {{ $borrower->user->name }} a rapporté un incident lors de son emprunt du {{
    $loan->loanable->name }} @if ($owner) appartenant à {{ $owner->user->name
    }}. @else appartenant à la communauté. @endif
</p>
@if (!!$incident->comments_on_incident)
<p
    style="
        text-align: center;
        margin-top: 0;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    Commentaires:
</p>
<p
    style="
        text-align: center;
        margin-top: 0;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    {{ $incident->comments_on_incident }}
</p>
@endif

<p style="text-align: center; margin: 32px auto 0 auto">
    @include('emails.partials.button', [
        'url' => env('FRONTEND_URL') . '/loans/'. $loan->id,
        'text' => 'Voir l\'emprunt'
    ])
</p>

@endsection
