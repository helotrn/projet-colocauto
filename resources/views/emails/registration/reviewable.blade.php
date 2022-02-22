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
    Un-e nouveau-lle membre, {{ $user->full_name }}, a complété son inscription
    dans {{ $community->name }} et peut être validé-e.
</p>

<table border="0" cellspacing="10" cellpadding="10">
    <tr>
        <td>
            <table border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td
                        align="center"
                        style="border-radius: 5px; background-color: #246aea"
                    >
                        <a
                            href="{{$user->admin_link}}"
                            target="_blank"
                            style="
                                font-size: 18px;
                                font-family: Helvetica, Arial, sans-serif;
                                color: #ffffff;
                                font-weight: bold;
                                text-decoration: none;
                                border-radius: 5px;
                                padding: 12px 18px;
                                border: 1px solid #246aea;
                                display: inline-block;
                            "
                            >Voir son profil</a
                        >
                    </td>
                </tr>
            </table>
        </td>
        <td>
            <table border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td
                        align="center"
                        style="border-radius: 5px; background-color: #246aea"
                    >
                        <a
                            href="{{ env('FRONTEND_URL') . '/admin/communities/' . $community->id }}#members"
                            target="_blank"
                            style="
                                font-size: 18px;
                                font-family: Helvetica, Arial, sans-serif;
                                color: #ffffff;
                                font-weight: bold;
                                text-decoration: none;
                                border-radius: 5px;
                                padding: 12px 18px;
                                border: 1px solid #246aea;
                                display: inline-block;
                            "
                            >Voir le voisinage</a
                        >
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@endsection
