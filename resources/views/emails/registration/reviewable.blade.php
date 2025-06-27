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
                        style="border-radius: 5px; background-color: @color(primary)"
                    >
                        @include('emails.partials.button', [
                            'url' => $user->admin_link,
                            'text' => 'Voir son profil'
                        ])
                    </td>
                </tr>
            </table>
        </td>
        <td>
            <table border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td
                        align="center"
                        style="border-radius: 5px; background-color: @color(primary)"
                    >
                        @include('emails.partials.button', [
                            'url' => env('FRONTEND_URL') . '/admin/communities/' . $community->id . '#members',
                            'text' => 'Voir le voisinage'
                        ])
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@endsection
