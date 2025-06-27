@extends('emails.layouts.main') @section('content')
<p
    style="
        text-align: center;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
        margin-bottom: 32px;
    "
>
    Bonjour {{ $owner->user->name }},
</p>

<p
    style="
        text-align: center;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    {{ $borrower->user->name }} a demandé à rallonger son emprunt de votre {{
    $loan->loanable->name }} qui commençait à {{ $loan->departure_at }} et qui
    durerait maintenant {{ $extension->new_duration }} minutes.
</p>

@if (!!$extension->comments_on_extension)
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
    Commentaires:
</p>
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
    {{ $extension->comments_on_extension }}
</p>
@endif

<p style="text-align: center; margin: 32px auto 0 auto">
    @include('emails.partials.button', [
        'url' => env('FRONTEND_URL') . '/loans/' . $loan->id,
        'text' => 'Voir l\'emprunt'
    ])
</p>

@endsection
