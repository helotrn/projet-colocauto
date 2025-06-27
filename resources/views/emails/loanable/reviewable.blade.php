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
    Un nouveau véhicule, {{ $loanable->name }}, a été ajouté par {{
    $user->full_name }} dans {{ $community->name }} et peut être validé.
</p>

<p style="text-align: center; margin: 32px auto 0 auto">
    @include('emails.partials.button', [
        'url' => env('FRONTEND_URL') . '/admin/loanables/' . $loanable->id,
        'text' => 'Voir le véhicule'
    ])
</p>
@endsection
