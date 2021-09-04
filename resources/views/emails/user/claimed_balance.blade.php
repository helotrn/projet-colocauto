@extends('emails.layouts.main') @section('content')
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
    {{ $user->name }} {{ $user->last_name }} a demandé à ce que la balance de
    son compte lui soit reversé.
</p>

<p style="text-align: center; margin: 32px auto 0 auto">
    <a
        href="{{ env('FRONTEND_URL') . '/admin/users/' . $user->id }}"
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
        >Voir le profil</a
    >
</p>
@endsection
