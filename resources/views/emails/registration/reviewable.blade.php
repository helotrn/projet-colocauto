@extends('emails.layouts.main')

@section('content')
<p>
    Un&bull;e nouveau&bull;lle membre, {{ $user->name }} {{ $user->last_name }}, a complété son inscription dans {{ $community->name }} et peut être validé&bull;e.
</p>

<p style="text-align: center;">
<a href="{{ url('/admin/communities/' . $community->id) }}#members" style="display: inline-block; background-color: #246AEA; padding: 10px; border-radius: 3px; color: white; font-weight: bold; text-decoration: none;" target="_blank">Voir le voisinage</a>
</p>
@endsection
