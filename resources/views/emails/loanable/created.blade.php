@extends('emails.layouts.main')

@section('content')
<p>
    Bonjour {{ $user->name }},
</p>
<p>
    Félicitations, votre véhicule {{ $loanable->name }} a bien été ajouté!
</p>

<p>
    Vous recevrez un message quand une personne du voisinage voudra l'utiliser.
</p>

<p>
    N'oubliez pas, en tout temps, vous pouvez modifier sa disponibilité.
</p>

<p style="text-align: right;">
    <em>- L'équipe LocoMotion</em>
</p>
@endsection
