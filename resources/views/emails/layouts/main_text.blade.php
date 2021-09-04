@include('emails.partials.header_text')

{{ $title }}

@yield('content')

@include('emails.partials.footer_text')
