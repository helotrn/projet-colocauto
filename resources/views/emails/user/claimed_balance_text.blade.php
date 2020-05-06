@extends('emails.layouts.main_text')

@section('content')
{{ $user->name }} {{ $user->last_name }} a demandé à ce que la balance de son compte lui soit reversé.

Voir le profil [https://locomotion.app/admin/users/{{ $user->id }}]
@endsection
