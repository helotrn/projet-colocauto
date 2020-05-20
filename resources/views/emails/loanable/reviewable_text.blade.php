@extends('emails.layouts.main_text')

@section('content')
Un nouveau véhicule, {{ $loanable->name }}, a été ajouté par {{ $user->name }} {{ $user->last_name }} dans {{ $community->name }} et peut être validé.

Voir le véhicule [{{ url('/admin/loanables/' . $loanable->id) }}]
@endsection
