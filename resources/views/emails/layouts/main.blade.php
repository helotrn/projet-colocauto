<html>
<body style="margin: 0; padding: 0;">
    <table style="width: 100%;">
        <tbody>
            @include('emails.partials.header')

            <tr>
                <td style="text-align: center;">
                    <table style="width: 600px; margin: 0 auto; padding: 40px 0 40px 0;">
                        <tr>
                            <td>
                                @yield('content')
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            @include('emails.partials.footer')
        </tbody>
    </table>
</body>
</html>
