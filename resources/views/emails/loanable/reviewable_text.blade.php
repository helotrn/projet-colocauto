@extends('emails.layouts.main')

@section('content')
Un nouveau véhicule, {{ $loanable->name }}, a été ajouté par {{ $user->name }} {{ $user->last_name }} dans {{ $community->name }} et peut être validé.

Voir le véhicule [https://locomotion.app/admin/loanables/{{ $loanable->id }}]
@endsection
