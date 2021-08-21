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
    Un nouveau véhicule, {{ $loanable->name }}, a été ajouté par {{ $user->name
    }} {{ $user->last_name }} dans {{ $community->name }} et peut être validé.
</p>

<p style="text-align: center; margin: 32px auto 0 auto">
    <a
        href="{{ env('FRONTEND_URL') . '/admin/loanables/' . $loanable->id }}"
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
        >Voir le véhicule</a
    >
</p>
@endsection
