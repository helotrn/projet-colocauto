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
                                Bonjour {{ $user->name }},
                            </p>

                            <p>
                                Votre profil est complété, bienvenue dans LocoMotion !
                            </p>
                            <p>
                                Vous pouvez maintenant partager auto, vélo ... et pédalo ? Ça, ça dépend de vos voisines et voisins. ;-)
                            </p>
                            <p style="text-align: center;">
                                <a href="https://locomotion.app/community" style="display: inline-block; background-color: #246AEA; padding: 10px; border-radius: 3px; color: white; font-weight: bold; text-decoration: none;" target="_blank">Voir mon voisinage</a>
                            </p>

                            <p style="text-align: right;">
                                <em>- L'équipe LocoMotion</em>
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
