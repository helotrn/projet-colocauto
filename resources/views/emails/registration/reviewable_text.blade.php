@include('emails.partial.header_text')

Un&bull;e nouveau&bull;lle membre, {{ $user->name }} {{ $user->last_name }}, a complété son inscription dans {{ $community->name }} et peut être validé&bull;e.

Voir le voisinage [https://locomotion.app/admin/communities/{{ $community->id }}#members]

@include('emails.partial.footer_text')
