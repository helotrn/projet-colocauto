@extends('emails.layouts.main_text')

@section('content')
Bonjour,

Vous avez été invité·e à créer un compte d'administration de communauté.
@if ($community)
    Vous aurez en charge l'administration du groupe {{ $community->name }}.
@endif

Créer mon compte [{{ env('FRONTEND_URL') . '/register/1?invitation=' . $token . '&email=' . urlencode($email) }}]

            - L'équipe Coloc'Auto
@endsection
