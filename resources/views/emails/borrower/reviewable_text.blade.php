@extends('emails.layouts.main_text')

@section('content')
Un·e nouveau·lle membre, {{ $user->name }}, a complété son profil d'emprunteur
dans les communautés suivantes :

@foreach ($communities as $community)
  - {{ $community->name }}
@endforeach

Le profil peut maintenant être validé.

Voir le profil [{{ env('FRONTEND_URL') . '/admin/users/' . $user->id) }}#borrower]
@endsection
