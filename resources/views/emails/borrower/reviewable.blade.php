@extends('emails.layouts.main')

@section('content')

<p style="text-align: justify; margin-top: 0; font-weight: 390; font-size: 17px; line-height: 24px; color: #343A40;">
    Un-e nouveau-lle membre, {{ $user->name }}, a complété son profil d'emprunteur
    dans les communautés suivantes&nbsp;:
</p>
<ul>
    @foreach ($communities as $community)
        <li style="text-align: justify; font-weight: 390; font-size: 17px; line-height: 24px; color: #343A40;">{{ $community->name }}</li>
    @endforeach
</ul>
<p style="text-align: justify; font-weight: 390; font-size: 17px; line-height: 24px; color: #343A40;">
    Le profil peut maintenant être validé.
</p>
<p style="text-align: center; margin-bottom: 0">
    <a href="{{ url('/admin/users/' . $user->id) }}#borrower" style="display: inline-block; background-color: #246AEA; padding: 8px 16px; border-radius: 5px; color: white; font-weight: bold; font-size: 17px; line-height: 24px; text-decoration: none;" target="_blank">Voir le profil</a>
</p>

@endsection
