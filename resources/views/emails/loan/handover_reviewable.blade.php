@extends('emails.layouts.main') @section('content')
<p
    style="
        text-align: justify;
        margin-top: 0;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    {{ $caller->name }} a contesté les données du retour sur son emprunt du {{
    $loan->loanable->name }} @if ($loan->loanable->owner) appartenant à {{
    $loan->loanable->owner->user->name }}. @else appartenant à la communauté.
    @endif
</p>

@if (!!$handover->comments_on_contestation)
<p
    style="
        text-align: justify;
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
        text-align: justify;
        margin-top: 0;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    {{ $handover->comments_on_contestation }}
</p>
@endif

<p style="text-align: center; margin: 32px auto 0 auto">
    <a
        href="{{ url('/loans/'. $loan->id) }}"
        style="
            display: inline-block;
            background-color: #246aea;
            padding: 8px 16px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            font-size: 17px;
            line-height: 24px;
            text-decoration: none;
        "
        target="_blank"
        >Voir l'emprunt</a
    >
</p>
@endsection
