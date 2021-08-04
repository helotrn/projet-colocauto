<html>
    <style type="text/css">
        * {
            font-family: "BrandonText", "-apple-system", "BlinkMacSystemFont",
                "Segoe UI", "Roboto", "Helvetica Neue", "Arial", "Noto Sans",
                "sans-serif", "Apple Color Emoji", "Segoe UI Emoji",
                "Segoe UI Symbol", "Noto Color emoji";
        }
        .monospace {
            font-family: "SFMono-Regular", "Menlo", "Monaco", "Consolas",
                "Liberation Mono", "Courier New", "monospace";
        }

        td {
            padding: 0;
        }
    </style>
    <body style="background-color: #f5f8fb">
        <table style="width: 100%; border-collapse: collapse">
            <tbody>
                @include('emails.partials.header')

                <tr>
                    <td style="text-align: center">
                        <table
                            style="
                                background-color: white;
                                width: 100%;
                                margin: 0 auto 32px auto;
                                padding: 0px 32px 44px 32px;
                                max-width: 536px;
                            "
                        >
                            <tr>
                                <td>@yield('content')</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                @include('emails.partials.footer')
            </tbody>
        </table>
    </body>
</html>
