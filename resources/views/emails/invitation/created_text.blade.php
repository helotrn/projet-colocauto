@extends('emails.layouts.main_text')

@section('content')
Bonjour,

Vous avez été invité·e à rejoindre le groupe {{ $community->name }} pour partager un ou plusieurs véhicules..

Créer mon compte [{{ env('FRONTEND_URL') . '/register/1?invitation=' . $token }}]

            - L'équipe Coloc'Auto
@endsection
