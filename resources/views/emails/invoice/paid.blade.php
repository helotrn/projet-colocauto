@extends('emails.layouts.main')

@section('content')
<table style="width: 100%;">
  <tbody>
    <tr style="display: flex; align-items: center;">
      <td style="text-align: left; width: 50%; padding-right: 16px;" align="top">
        LocoMotion<br>
        Solon collectif (Celsius Mtl)<br>
        6450, Ave Christophe-Colomb<br>
        Montréal, QC<br>
        H2S 2G7
      </td>

      <td style="text-align: right; width: 50%; padding-left: 16px;" align="top">
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
      <td colspan="3" style="padding: 20px 0; border-top: 1px solid black; border-bottom: 1px solid black;">
        <strong class="monospace">{{ $invoice['id'] }}</strong> &bull; <strong class="monospace">{{ $invoice['period'] }}</strong>
      </td>
    </tr>

    <tr>
      <td>
        <table style="width: 100%">
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
                <td class="monospace" style="padding: 10px 0 10px 20px; text-align: right;">{{ $item['item_date'] }}</td>
                <td style="padding: 10px 0 10px 20px; text-align: right;">{{ $item['label'] }}</td>
                <td class="monospace" style="padding: 10px 0 10px 20px; text-align: right;">@money($item['amount'])</td>
              </tr>
            @endforeach
            <tr>
              <td>
                <br>
              </td>
            </tr>
            <tr>
              <td colspan="2" style="text-align: right;">
                <strong>Sous-total</strong>
              </td>
              <td class="monospace" style="text-align: right;">
                @money($invoice['total'])
              </td>
            </tr>
            <tr>
              <td colspan="2" style="text-align: right;">
                <strong>TPS</strong>
              </td>
              <td style="text-align: right;" class="monospace">
                @money($invoice['total_tps'])
              </td>
            </tr>
            <tr>
              <td colspan="2" style="text-align: right;">
                <strong>TVQ</strong>
              </td>
              <td class="monospace" style="text-align: right;">
                @money($invoice['total_tvq'])
              </td>
            </tr>
            <tr>
              <td colspan="2" style="text-align: right;">
                <strong>Total</strong>
              </td>
              <td class="monospace" style="text-align: right;">
                @money($invoice['total_with_taxes'])
              </td>
            </tr>
          </tbody>
        </table>
      </td>
    </tr>
</table>

<br>

<p style="text-align: center;">
  Merci!
</p>

<br>

<p style="text-align: right; padding-right: 10px">
  <em>- L'équipe LocoMotion</em>
</p>
@endsection
