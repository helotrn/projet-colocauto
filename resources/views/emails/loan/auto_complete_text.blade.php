@extends('emails.layouts.main_text')

@section('content')
Bonjour {{ $user->name }},

Votre réservation de {{ $loan->loanable->name }} du {{ \Carbon\Carbon::parse($loan->departure_at)->isoFormat('LL') }} n'a pas été complétée par vos soins.

Elle a été clôturée automatiquement avec les kilomètres estimés. Vous pouvez retrouver et modifier la dépense générée dans la page dépense [{{ env('FRONTEND_URL') . '/wallet/expenses' }}].

        - L'équipe Coloc'Auto
@endsection
