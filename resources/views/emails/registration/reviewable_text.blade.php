@extends('emails.layouts.main_text')

@section('content')
Un·e nouveau·lle membre, {{ $user->name }}, a complété son inscription dans {{ $community->name }} et peut être validé·e.

Voir le voisinage [{{ url('/admin/communities/' . $community->id) }}#members]
@endsection
