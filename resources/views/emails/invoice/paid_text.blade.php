@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $user->name }},

Vous trouvez ci-contre le relevé de votre plus récent paiement sur LocoMotion.

LocoMotion
Solon collectif (Celsius Mtl)
5985, rue St-Hubert
Montréal, QC
H2S 2L8

{{ $user->name }} {{ $user->last_name }}
{{ $user->address }}
{{ $user->postal_code }}

{{ $invoice['id'] }} * {{ $invoice['period'] }}

@foreach ($invoice['bill_items'] as $item)
Date: {{ $item['item_date'] }}
Description: {{ $item['label'] }}
Montant: @money($item['amount'])

@endforeach

Sous-total:
@money($invoice['total'])

TPS:
@money($invoice['total_tps'])

TVQ:
@money($invoice['total_tvq'])

Total
@money($invoice['total_with_taxes'])


Merci!

            - L'équipe LocoMotion
@endsection
