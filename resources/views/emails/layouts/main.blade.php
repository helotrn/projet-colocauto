<html>
<style type="text/css">
    * {
        font-family: 'BrandonText', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'Noto Sans', 'sans-serif', 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color emoji';
    }
    .monospace {
        font-family: "SFMono-Regular", 'Menlo', 'Monaco', 'Consolas', "Liberation Mono", "Courier New", 'monospace';
    }
</style>
<body style="background-color: #F5F8FB;">
    <table style="width: 100%;">
        <tbody>
            @include('emails.partials.header')

            <tr>
                <td style="text-align: center;">
                    <table style="background-color: white; width: 100%; margin: 0 auto 32px auto; padding: 40px 0; max-width: 536px;">
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
