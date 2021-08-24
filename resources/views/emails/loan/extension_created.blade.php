@extends('emails.layouts.main') @section('content')
<p style="
    text-align: center;
    font-weight: 390;
    font-size: 17px;
    line-height: 24px;
    color: #343a40;
    margin-bottom: 32px;
    "
>
    Bonjour {{ $owner->user->name }},
</p>

<p
    style="
        text-align: center;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    {{ $borrower->user->name }} a demandé à rallonger son emprunt de votre {{
    $loan->loanable->name }} qui commençait à {{ $loan->departure_at }} et qui
    durerait maintenant {{ $extension->new_duration }} minutes.
</p>

@if (!!$extension->comments_on_extension)
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
    {{ $extension->comments_on_extension }}
</p>
@endif

<p style="text-align: center; margin: 32px auto 0 auto">
    <a
        href="{{ env('FRONTEND_URL') . '/loans/' . $loan->id }}"
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
