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
    Vous avez été invité·e à créer un compte d'administration de communauté.
    @if ($community)
        Vous aurez en charge l'administration du groupe {{ $community->name }}.
    @endif
</p>

<br />

<p style="text-align: center; margin: 32px auto 0 auto">
    @include('emails.partials.button', [
        'url' => env('FRONTEND_URL') . '/register/1?invitation=' . $token . '&email=' . urlencode($email),
        'text' => 'Créer mon compte'
    ])
</p>

@endsection
