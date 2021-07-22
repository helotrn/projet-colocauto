<tr>
    <td align="center" style="height: 60px;">
        <div style="text-align: center;">
            <img src="{{ url('/mail-header-logo.png') }}" alt="LocoMotion" style="width: 125px; height: 17px;">
        </div>
    </td>
</tr>
<tr>
    <td align="center">
        <table style="width: 100%; max-width: 536px; background-color: white; padding: 44px 0;">
            <tr>
                <td>
                    <h1 style="text-align: center; font-weight: 420; font-size: 32px; line-height: 40px; color: #343A40;">
                        {{ $title }}
                    </h1>
                    <p style="text-align: center; font-weight: 390; font-size: 17px; line-height: 24px; color: #343A40;">
                        Bonjour {{ $user->name }},
                    </p>
                    <p style="text-align: center; font-weight: 390; font-size: 17px; line-height: 24px; color: #343A40;">
                        {!! $text !!}
                        <br>
                    </p>
                </td>
            </tr>
        </table>
    </td>
</tr>
