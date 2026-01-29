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
    Bonjour {{ $owner->user->name }},
</p>

<p
    style="
        text-align: center;
        margin-top: 0;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    {{ $borrower->user->name }} a reservé votre véhicule {{
    $loan->loanable->name }} à partir de @datetime($loan->departure_at) et pour une
    durée de @duration($loan->duration_in_minutes).
</p>

<br />

@if (!!$loan->message_for_owner)
<p
    style="
        text-align: center;
        margin-top: 0;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    Commentaires:
</p>

<p
    style="
        text-align: center;
        margin-top: 0;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    {{ $loan->message_for_owner }}
</p>
@endif

//Code d'acces a la boite a cles
@if(!empty($loan->unique_access_code))
    <div style="margin: 32px 0; padding: 20px; background-color: #f0fdf4; border: 2px dashed #16a34a; border-radius: 8px; text-align: center;">
        
        <h3 style="margin: 0 0 10px 0; color: #15803d; font-family: Helvetica, Arial, sans-serif; font-size: 18px;">
            🔑 Code Boîte à Clés
        </h3>
        
        <p style="margin: 0 0 15px 0; color: #374151; font-size: 14px;">
            Code d'accès pour ce créneau :
        </p>
        
        <div style="font-size: 32px; font-weight: bold; color: #111827; letter-spacing: 8px; background: white; padding: 10px; display: inline-block; border-radius: 4px; border: 1px solid #ddd;">
            {{ $loan->unique_access_code }}
        </div>

        <p style="margin: 15px 0 0 0; font-size: 12px; color: #6b7280; font-style: italic;">
            Validité :
            {{ \Carbon\Carbon::parse($loan->departure_at)->subMinutes(5)->format('H:i') }}
            à
            {{ \Carbon\Carbon::parse($loan->departure_at)->addMinutes($loan->duration_in_minutes + 5)->format('H:i') }}
        </p>
    </div>
    @endif
<p style="text-align: center; margin: 32px auto 0 auto">
    @include('emails.partials.button', [
        'url' => env('FRONTEND_URL') . '/loans/' . $loan->id,
        'text' => 'Voir l\'emprunt'
    ])
</p>

@endsection
