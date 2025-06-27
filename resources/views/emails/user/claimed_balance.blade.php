@extends('emails.layouts.main') @section('content')
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
    {{ $user->full_name }} a demandé à ce que la balance de son compte lui soit
    reversé.
</p>

<p style="text-align: center; margin: 32px auto 0 auto">
    @include('emails.partials.button', [
        'url' => env('FRONTEND_URL') . '/admin/users/' . $user->id,
        'text' => 'Voir le profil'
    ])
</p>
@endsection
