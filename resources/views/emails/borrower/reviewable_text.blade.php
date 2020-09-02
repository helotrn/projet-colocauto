@extends('emails.layouts.main_text')

@section('content')
Un·e nouveau·lle membre, {{ $user->name }}, a complété son profil d'emprunteur
dans {{ $community->name }} et peut être validé·e.

Voir le profil [{{ url('/admin/users/' . $user->id) }}#borrower]
@endsection
