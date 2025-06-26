@extends('emails.layouts.main_text')

@section('content')
Bonjour,

Vous avez été invité·e à créer un compte sur Coloc'Auto pour lancer votre communauté et partager un ou plusieurs véhicules.

Créer mon compte [{{ env('FRONTEND_URL') . '/register/1?invitation=' . $token . '&email=' . urlencode($email) }}]

            - L'équipe Coloc'Auto
@endsection
