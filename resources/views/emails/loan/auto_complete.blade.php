@extends('emails.layouts.main') @section('content')
<p
    style="
        text-align: center;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
        margin-bottom: 32px;
    "
>
    Bonjour {{ $user->name }},
</p>

<p
    style="
        text-align: center;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    Votre réservation de {{ $loan->loanable->name }} du {{ \Carbon\Carbon::parse($loan->departure_at)->isoFormat('LL') }} n'a pas été complétée par vos soins.
</p>

<p
    style="
        text-align: center;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    Elle a été clôturée automatiquement avec les kilomètres estimés. Vous pouvez retrouver et modifier la dépense générée dans la page dépense. 
</p>


<p style="text-align: center; margin-top: 32px">
    <a
        href="{{ env('FRONTEND_URL') . '/wallet/expenses' }}"
        style="
            display: inline-block;
            background-color: #246aea;
            padding: 8px 16px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            font-size: 17px;
            line-height: 24px;
            text-decoration: none;
        "
        target="_blank"
        >Voir les dépenses</a
    >
</p>

@endsection
