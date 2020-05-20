@extends('emails.layouts.main')

@section('content')
<p>
    Bonjour {{ $user->name }},
</p>

<p>
    Votre profil est complété, bienvenue dans LocoMotion !
</p>
<p>
    Vous pouvez maintenant partager auto, vélo ... et pédalo ? Ça, ça dépend de vos voisines et voisins. ;-)
</p>
<p style="text-align: center;">
    <a href="{{ url('/community') }}" style="display: inline-block; background-color: #246AEA; padding: 10px; border-radius: 3px; color: white; font-weight: bold; text-decoration: none;" target="_blank">Voir mon voisinage</a>
</p>

<p style="text-align: right;">
    <em>- L'équipe LocoMotion</em>
</p>
@endsection
