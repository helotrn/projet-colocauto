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
    Un-e nouveau-lle membre, {{ $user->name }}, a complété son profil
    d'emprunteur dans les communautés suivantes&nbsp;:
</p>
<ul>
    @foreach ($communities as $community)
    <li
        style="
            text-align: left;
            font-weight: 390;
            font-size: 17px;
            line-height: 24px;
            color: #343a40;
        "
    >
        {{ $community->name }}
    </li>
    @endforeach
</ul>
<p
    style="
        text-align: center;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
        margin: 32px auto;
    "
>
    Le profil peut maintenant être validé.
</p>
<p style="text-align: center; margin-bottom: 0">
    @include('emails.partials.button', [
        'url' => env('FRONTEND_URL'). '/admin/users/' . $user->id . '#borrower',
        'text' => 'Voir le profil'
    ])
</p>

@endsection
