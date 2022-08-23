@extends('emails.layouts.main_text')

@section('content')
Un nouveau véhicule, {{ $loanable->name }}, a été ajouté par {{ $user->full_name }} dans {{ $community->name }} et peut être validé.

Voir le véhicule [{{ env('FRONTEND_URL') . '/admin/loanables/' . $loanable->id }}]
@endsection
