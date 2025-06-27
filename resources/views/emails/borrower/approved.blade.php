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
    Bonjour {{ $user->name }},
</p>

<p
    style="
        text-align: center;
        margin: 0;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    Félicitations, votre dossier de conduite est approuvé! Vous pouvez
    maintenant emprunter les autos de vos voisins et voisines ;-)
</p>
<p style="text-align: center; margin-top: 32px; margin-bottom: 0">
    @include('emails.partials.button', [
        'url' => env('FRONTEND_URL') . '/search/map',
        'text' => 'Voir mon voisinage'
    ])
</p>

@endsection
