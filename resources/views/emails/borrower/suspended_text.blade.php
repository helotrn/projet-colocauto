@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $user->name }},

Votre compte a été suspendu par un admin.

L'équipe Coloc'Auto
soutien@colocauto.org
@endsection
