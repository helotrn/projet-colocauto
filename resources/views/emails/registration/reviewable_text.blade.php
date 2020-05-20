@extends('emails.layouts.main_text')

@section('content')
Un&bull;e nouveau&bull;lle membre, {{ $user->name }} {{ $user->last_name }}, a complété son inscription dans {{ $community->name }} et peut être validé&bull;e.

Voir le voisinage [{{ url('/admin/communities/' . $community->id) }}#members]
@endsection
