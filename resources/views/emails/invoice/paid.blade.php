@extends('emails.layouts.main') @section('content')

<p
    style="
        text-align: justify;
        margin: 0;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    Bonjour {{ $user->name }},
</p>

<p
    style="
        text-align: justify;
        margin: 0;
        padding-bottom: 32px;
        font-weight: 390;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    {!! $text !!}
</p>

<table style="width: 100%">
    <tbody>
        <tr style="display: flex; align-items: center">
            <td
                style="
                    text-align: left;
                    width: 50%;
                    padding-right: 16px;
                    font-weight: 390;
                    font-size: 17px;
                    line-height: 24px;
                    color: #343a40;
                "
                align="top"
            >
                LocoMotion<br />
                Solon collectif (Celsius Mtl)<br />
                6450, Ave Christophe-Colomb<br />
                Montréal, QC<br />
                H2S 2G7
            </td>

            <td
                style="
                    text-align: right;
                    width: 50%;
                    padding-left: 16px;
                    font-weight: 390;
                    font-size: 17px;
                    line-height: 24px;
                    color: #343a40;
                "
                align="top"
            >
                {{ $user->name }} {{ $user->last_name }}<br />
                {{ $user->address }}<br />
                {{ $user->postal_code }}
            </td>
        </tr>
    </tbody>
</table>

<table style="width: 100%">
    <tbody>
        <tr>
            <td
                colspan="3"
                style="
                    padding: 20px 0;
                    border-top: 1px solid #343a40;
                    border-bottom: 1px solid #343a40;
                    font-size: 17px;
                    line-height: 24px;
                    color: #343a40;
                "
            >
                <strong class="monospace">{{ $invoice['id'] }}</strong> &bull;
                <strong class="monospace">{{ $invoice['period'] }}</strong>
            </td>
        </tr>

        <tr>
            <td>
                <table style="width: 100%">
                    <thead>
                        <tr>
                            <th
                                style="
                                    border-bottom: 1px solid #343a40;
                                    padding-bottom: 5px;
                                    font-size: 17px;
                                    line-height: 24px;
                                    color: #343a40;
                                "
                            >
                                Date
                            </th>
                            <th
                                style="
                                    border-bottom: 1px solid #343a40;
                                    padding-bottom: 5px;
                                    font-size: 17px;
                                    line-height: 24px;
                                    color: #343a40;
                                "
                            >
                                Description
                            </th>
                            <th
                                style="
                                    border-bottom: 1px solid #343a40;
                                    padding-bottom: 5px;
                                    font-size: 17px;
                                    line-height: 24px;
                                    color: #343a40;
                                "
                            >
                                Montant
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($invoice['bill_items'] as $item)
                        <tr>
                            <td
                                class="monospace"
                                style="
                                    padding: 10px 0 10px 20px;
                                    text-align: right;
                                    font-size: 17px;
                                    line-height: 24px;
                                    color: #343a40;
                                "
                            >
                                {{ $item['item_date'] }}
                            </td>
                            <td
                                style="
                                    padding: 10px 0 10px 20px;
                                    text-align: right;
                                    font-size: 17px;
                                    line-height: 24px;
                                    color: #343a40;
                                "
                            >
                                {{ $item['label'] }}
                            </td>
                            <td
                                class="monospace"
                                style="
                                    padding: 10px 0 10px 20px;
                                    text-align: right;
                                    font-size: 17px;
                                    line-height: 24px;
                                    color: #343a40;
                                "
                            >
                                @money($item['amount'])
                            </td>
                        </tr>
                        @endforeach
                        <tr>
                            <td>
                                <br />
                            </td>
                        </tr>
                        <tr>
                            <td
                                colspan="2"
                                style="
                                    text-align: right;
                                    font-size: 17px;
                                    line-height: 24px;
                                    color: #343a40;
                                "
                            >
                                <strong>Sous-total</strong>
                            </td>
                            <td
                                class="monospace"
                                style="
                                    text-align: right;
                                    font-size: 17px;
                                    line-height: 24px;
                                    color: #343a40;
                                "
                            >
                                @money($invoice['total'])
                            </td>
                        </tr>
                        <tr>
                            <td
                                colspan="2"
                                style="
                                    text-align: right;
                                    font-size: 17px;
                                    line-height: 24px;
                                    color: #343a40;
                                "
                            >
                                <strong>TPS</strong>
                            </td>
                            <td
                                class="monospace"
                                style="
                                    text-align: right;
                                    font-size: 17px;
                                    line-height: 24px;
                                    color: #343a40;
                                "
                            >
                                @money($invoice['total_tps'])
                            </td>
                        </tr>
                        <tr>
                            <td
                                colspan="2"
                                style="
                                    text-align: right;
                                    font-size: 17px;
                                    line-height: 24px;
                                    color: #343a40;
                                "
                            >
                                <strong>TVQ</strong>
                            </td>
                            <td
                                class="monospace"
                                style="
                                    text-align: right;
                                    font-size: 17px;
                                    line-height: 24px;
                                    color: #343a40;
                                "
                            >
                                @money($invoice['total_tvq'])
                            </td>
                        </tr>
                        <tr>
                            <td
                                colspan="2"
                                style="
                                    text-align: right;
                                    font-size: 17px;
                                    line-height: 24px;
                                    color: #343a40;
                                "
                            >
                                <strong>Total</strong>
                            </td>
                            <td
                                class="monospace"
                                style="
                                    text-align: right;
                                    font-size: 17px;
                                    line-height: 24px;
                                    color: #343a40;
                                "
                            >
                                @money($invoice['total_with_taxes'])
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>

<br />

<p
    style="
        text-align: center;
        margin-bottom: 0;
        font-size: 17px;
        line-height: 24px;
        color: #343a40;
    "
>
    Merci!
</p>

<br />

@endsection
