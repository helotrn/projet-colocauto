@extends('emails.layouts.main')

@section('content')
<p>
    Un&bull;e nouveau&bull;lle membre, {{ $user->name }}, a complété son profil d'emprunteur
    dans les communautés suivantes&nbsp;:
</p>
<ul>
    @foreach ($communities as $community)
        <li>{{ $community->name }}</li>
    @endforeach
</ul>
<p>
    Le profil peut maintenant être validé.
</p>

<p style="text-align: center;">
<a href="{{ url('/admin/users/' . $user->id) }}#borrower" style="display: inline-block; background-color: #246AEA; padding: 10px; border-radius: 3px; color: white; font-weight: bold; text-decoration: none;" target="_blank">Voir le profil</a>
</p>
@endsection
