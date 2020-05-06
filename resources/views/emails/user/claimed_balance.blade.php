@extends('emails.layouts.main')

@section('content')
<p>
    {{ $user->name }} {{ $user->last_name }} a demandé à ce que la balance de son compte lui
    soit reversé.
</p>

<p style="text-align: center;">
<a href="https://locomotion.app/admin/users/{{ $user->id }}" style="display: inline-block; background-color: #246AEA; padding: 10px; border-radius: 3px; color: white; font-weight: bold; text-decoration: none;" target="_blank">Voir le profil</a>
</p>
@endsection
