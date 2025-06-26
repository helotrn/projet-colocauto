@extends('emails.layouts.main') @section('content')
<p
    style="
        text-align: center;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
        margin-bottom: 32px;
    "
>
    Bonjour,
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
    Vous avez été invité·e à créer un compte sur Coloc'Auto pour lancer votre communauté et partager un ou plusieurs véhicules.
</p>

<br />

<p style="text-align: center; margin: 32px auto 0 auto">
    <a
        href="{{ env('FRONTEND_URL') . '/register/1?invitation=' . $token . '&email=' . urlencode($email) }}"
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
        >Créer mon compte</a
    >
</p>

@endsection
