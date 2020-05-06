<html>
<body style="margin: 0; padding: 0;">
    <table style="width: 100%;">
        @include('emails.partial.header')

        <tr>
            <td style="text-align: center;">
                <table style="width: 600px; margin: 0 auto; padding: 40px 0 40px 0;">
                    <tr>
                        <td>
                            <p>
                                Un&bull;e nouveau&bull;lle membre, {{ $user->name }} {{ $user->last_name }}, a complété son inscription dans {{ $community->name }} et peut être validé&bull;e.
                            </p>

                            <p style="text-align: center;">
                            <a href="https://locomotion.app/admin/communities/{{ $community->id }}#members" style="display: inline-block; background-color: #246AEA; padding: 10px; border-radius: 3px; color: white; font-weight: bold; text-decoration: none;" target="_blank">Voir le voisinage</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        @include('emails.partial.footer')
    </table>
</body>
</html>
