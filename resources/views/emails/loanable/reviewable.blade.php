@extends('emails.layouts.main')

@section('content')
<p>
    Un nouveau véhicule, {{ $loanable->name }}, a été ajouté par {{ $user->name }} {{ $user->last_name }} dans {{ $community->name }} et peut être validé.
</p>

<p style="text-align: center;">
<a href="{{ url('/admin/loanables/' . $loanable->id) }}" style="display: inline-block; background-color: #246AEA; padding: 10px; border-radius: 3px; color: white; font-weight: bold; text-decoration: none;" target="_blank">Voir le véhicule</a>
</p>
@endsection
