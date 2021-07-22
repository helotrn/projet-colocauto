@extends('emails.layouts.main')

@section('content')

<p style="text-align: justify; margin-top: 0; font-weight: 390; font-size: 17px; line-height: 24px; color: #343A40;">
    Félicitations, votre dossier de conduite est approuvé!
</p>
<p style="text-align: justify; font-weight: 390; font-size: 17px; line-height: 24px; color: #343A40;">
    Vous pouvez maintenant emprunter les autos de vos voisins et voisines ;-)
</p>
<p style="text-align: center; margin-bottom: 0;">
    <a href="{{ url('/community/list') }}" style="display: inline-block; background-color: #246AEA; padding: 8px 16px; border-radius: 5px; color: white; font-weight: bold; font-size: 17px; line-height: 24px; text-decoration: none;" target="_blank">
        Voir mon voisinage
    </a>
</p>

@endsection
