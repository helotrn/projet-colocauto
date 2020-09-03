@extends('emails.layouts.main')

@section('content')
<p>
    Bonjour {{ $user->name }},
</p>

<p>
    {!! $text !!}
</p>

<table style="width: 100%;">
  <tbody>
    <tr>
      <td style="text-align: left;" align="top">
        LocoMotion<br>
        Solon collectif (Celsius Mtl)<br>
        5985, rue St-Hubert<br>
        Montréal, QC<br>
        H2S 2L8
      </td>

      <td style="text-align: right;" align="top">
        {{ $user->name }} {{ $user->last_name }}<br>
        {{ $user->address }}<br>
        {{ $user->postal_code }}
      </td>
    </tr>
  </tbody>
</table>

<table style="width: 100%;">
  <tbody>
    <tr>
      <td colspan="3" style="padding: 20px 0 20px 0; border-top: 1px solid black; border-bottom: 1px solid black;">
        <strong>{{ $invoice['id'] }}</strong> &bull; <strong>{{ $invoice['period'] }}</strong>
      </td>
    </tr>

    <tr>
      <td>
        <table>
          <thead>
            <tr>
              <th style="border-bottom: 1px solid black; padding-bottom: 5px;">Date</th>
              <th style="border-bottom: 1px solid black; padding-bottom: 5px;">Description</th>
              <th style="border-bottom: 1px solid black; padding-bottom: 5px;">Montant</th>
            </tr>
          </thead>

          <tbody>
            @foreach ($invoice['bill_items'] as $item)
              <tr>
                <td style="padding: 10px 20px 10px 0;">{{ $item['item_date'] }}</td>
                <td style="padding: 10px 20px 10px 20px;">{{ $item['label'] }}</td>
                <td style="padding: 10px 0 10px 20px;">@money($item['amount'])</td>
              </tr>
            @endforeach
            <tr>
              <td colspan="2" style="text-align: right;">
                <strong>Sous-total</strong>
              </td>
              <td style="text-align: right;">
                @money($invoice['total'])
              </td>
            </tr>
            <tr>
              <td colspan="2" style="text-align: right;">
                <strong>TPS</strong>
              </td>
              <td style="text-align: right;">
                @money($invoice['total_tps'])
              </td>
            </tr>
            <tr>
              <td colspan="2" style="text-align: right;">
                <strong>TVQ</strong>
              </td>
              <td style="text-align: right;">
                @money($invoice['total_tvq'])
              </td>
            </tr>
            <tr>
              <td colspan="2" style="text-align: right;">
                <strong>Total</strong>
              </td>
              <td style="text-align: right;">
                @money($invoice['total_with_taxes'])
              </td>
            </tr>
          </tbody>
        </table>
      </td>
    </tr>
</table>

<p style="text-align: center;">
    Merci!
</p>

<p style="text-align: right;">
    <em>- L'équipe LocoMotion</em>
</p>
@endsection
