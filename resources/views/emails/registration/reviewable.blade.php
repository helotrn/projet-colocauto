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
    Un-e nouveau-lle membre, {{ $user->name }}, a complété son inscription dans
    {{ $community->name }} et peut être validé-e.
</p>

<p style="text-align: center; margin: 32px auto 0 auto">
    <a
        href="{{ env('FRONTEND_URL') . '/admin/communities/' . $community->id }}#members"
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
        >Voir le voisinage</a
    >
</p>
@endsection
