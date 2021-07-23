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
    Bonjour {{ $target->user->name }},
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
    L'incident rapporté lors de l'emprunt de votre {{ $loan->loanable->name }}
    qui commençait à {{ $loan->departure_at }} a été résolu.
</p>

<p style="text-align: center; margin: 32px auto 0 auto">
    <a
        href="{{ url('/loans/' . $loan->id) }}"
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
