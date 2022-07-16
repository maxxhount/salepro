<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="{{url('public/logo', $general_setting->site_logo)}}" />
    <title>{{$general_setting->site_title}}</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">

    <style type="text/css">
        * {
            font-size: 14px;
            line-height: 24px;
            font-family: 'Ubuntu', sans-serif;
            /* text-transform: capitalize; */
        }
        .btn {
            padding: 7px 10px;
            text-decoration: none;
            border: none;
            display: block;
            text-align: center;
            margin: 7px;
            cursor:pointer;
        }

        .btn-info {
            background-color: #999;
            color: #FFF;
        }

        .btn-primary {
            background-color: #6449e7;
            color: #FFF;
            width: 100%;
        }
        td,
        th,
        tr,
        table {
            border-collapse: collapse;
        }
        tr {border-bottom: 0px dotted #ddd;}
        td,th {padding: 0px 0;width: 50%;}
        hr{
            margin: 0px;
            border-top-style: dashed;
        }

        table {width: 100%;}
        tfoot tr th:first-child {text-align: left;}

        .centered {
            text-align: center;
            align-content: center;
        }
        small{font-size:11px;}

        @media print {
            * {
                font-size:12px;
                line-height: 20px;
            }
            td,th {padding: 0px 0;}
            .hidden-print {
                display: none !important;
            }
            @page { margin: 1.5cm 0.5cm 0.5cm; }
            @page:first { margin-top: 0.5cm; }
            tbody::after {
                content: ''; display: block;
                page-break-after: always;
                page-break-inside: avoid;
                page-break-before: avoid;
            }
        }
    </style>
  </head>
<body>

<div style="max-width:400px;margin:0 auto">
    {{-- @if(preg_match('~[0-9]~', url()->previous()))
        @php $url = '../../pos'; @endphp
    @else --}}
        @php $url = url()->previous(); @endphp
    {{-- @endif --}}
    <div class="hidden-print">
        <table>
            <tr>
                <td><a href="{{$url}}" class="btn btn-info"><i class="fa fa-arrow-left"></i> {{trans('file.Back')}}</a> </td>
                <td><button onclick="window.print();" class="btn btn-primary"><i class="dripicons-print"></i> {{trans('file.Print')}}</button></td>
            </tr>
        </table>
        <br>
    </div>

    <div id="receipt-data">
        <div class="centered">
            @if($general_setting->site_logo)
                <img src="{{url('public/logo', $general_setting->site_logo)}}" height="42" width="50" style="margin:10px 0;filter: brightness(0);">
            @endif

            <h1><b style="font-size: 24px">{{$lims_biller_data->company_name}}</b></h1>
            <p>{{$lims_warehouse_data->activity}}</p>

            <p>{{$lims_warehouse_data->address}}
                <br>{{$lims_warehouse_data->phone}}
            </p>
        </div>
        <p>{{trans('file.Date')}}: {{date($general_setting->date_format, strtotime($lims_sale_data->created_at->toDateString()))}}<br>
            {{trans('file.reference')}}: {{$lims_sale_data->reference_no}}<br>
            {{trans('file.customer')}}: {{$lims_customer_data->name}}
        </p>
        <table class="table-data">
            <tbody>
                <?php $total_product_tax = 0;?>
                @foreach($lims_product_sale_data as $key => $product_sale_data)
                <?php
                    $lims_product_data = \App\Product::find($product_sale_data->product_id);
                    if($product_sale_data->variant_id) {
                        $variant_data = \App\Variant::find($product_sale_data->variant_id);
                        $product_name = $lims_product_data->name.' ['.$variant_data->name.']';
                    }
                    elseif($product_sale_data->product_batch_id) {
                        $product_batch_data = \App\ProductBatch::select('batch_no')->find($product_sale_data->product_batch_id);
                        $product_name = $lims_product_data->name.' ['.trans("file.Batch No").':'.$product_batch_data->batch_no.']';
                    }
                    else
                        $product_name = $lims_product_data->name;

                    if($product_sale_data->imei_number) {
                        $product_name .= '<br>'.trans('IMEI or Serial Numbers').': '.$product_sale_data->imei_number;
                    }
                ?>
                <tr>
                    <td colspan="5">
                        <table>
                            <tbody>
                                <tr>
                                    <td colspan="5"><div>{!!$product_name!!}</div></td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        {{$product_sale_data->qty}} x {{number_format((float)($product_sale_data->total / $product_sale_data->qty), 0, '.', ' ')}}
                                    </td>
                                    <td style="text-align:right;vertical-align:bottom">{{number_format((float)$product_sale_data->total, 0, '.', ' ')}}</td>
                                </tr>
                            </tbody>
                        </table>
                        <hr>
                        <div></div>

                        {{-- @if($product_sale_data->tax_rate)
                            <?php ://$total_product_tax += $product_sale_data->tax ?>
                            [{{trans('file.Tax')}} ({{$product_sale_data->tax_rate}}%): {{$product_sale_data->tax}}]
                        @endif --}}
                    </td>

                </tr>
                @endforeach

            <!-- <tfoot> -->
                <tr>
                    <th colspan="4" style="text-align:left">{{trans('file.Total')}}</th>
                    <th style="text-align:right">{{number_format((float)$lims_sale_data->total_price, 0, '.', ' ')}}</th>
                </tr>

                @if($general_setting->invoice_format == 'gst' && $general_setting->state == 1)
                <tr>
                    <td colspan="4">IGST</td>
                    <td style="text-align:right">{{number_format((float)$total_product_tax, 0, '.', ' ')}}</td>
                </tr>
                @elseif($general_setting->invoice_format == 'gst' && $general_setting->state == 2)
                <tr>
                    <td colspan="4">SGST</td>
                    <td style="text-align:right">{{number_format((float)($total_product_tax / 2), 0, '.', ' ')}}</td>
                </tr>
                <tr>
                    <td colspan="4">CGST</td>
                    <td style="text-align:right">{{number_format((float)($total_product_tax / 2), 0, '.', ' ')}}</td>
                </tr>
                @endif
                @if($lims_sale_data->order_tax)
                <tr>
                    <th colspan="4" style="text-align:left">{{trans('file.Order Tax')}} ({{  $lims_sale_data->order_tax_rate}})</th>
                    <th style="text-align:right">{{number_format((float)$lims_sale_data->order_tax, 0, '.', ' ')}}</th>
                </tr>
                @endif
                @if($lims_sale_data->order_discount)
                <tr>
                    <th colspan="4" style="text-align:left">{{trans('file.Order Discount')}}</th>
                    <th style="text-align:right">{{number_format((float)$lims_sale_data->order_discount, 0, '.', ' ')}}</th>
                </tr>
                @endif
                @if($lims_sale_data->coupon_discount)
                <tr>
                    <th colspan="4" style="text-align:left">{{trans('file.Coupon Discount')}}</th>
                    <th style="text-align:right">{{number_format((float)$lims_sale_data->coupon_discount, 0, '.', ' ')}}</th>
                </tr>
                @endif
                @if($lims_sale_data->shipping_cost)
                <tr>
                    <th colspan="4" style="text-align:left">{{trans('file.Shipping Cost')}}</th>
                    <th style="text-align:right">{{number_format((float)$lims_sale_data->shipping_cost, 0, '.', ' ')}}</th>
                </tr>
                @endif
                <tr>
                    <th colspan="4" style="text-align:left;font-size:18px">{{trans('file.grand total')}}</th>
                    <th style="text-align:right;font-size:18px">{{number_format((float)$lims_sale_data->grand_total, 0, '.', ' ')}} <span>{{$currency->name}}</span></th>
                </tr>
                <tr>
                    @if($general_setting->currency_position == 'prefix')
                    <th class="centered" colspan="5"><span style="text-transform: capitalize">{{$currency->code}}</span> <span>{{str_replace("-"," ",$numberInWords)}}</span></th>
                    @else
                    <th class="centered" colspan="5"><span style="text-transform: capitalize">{{str_replace("-"," ",$numberInWords)}}</span> <span>{{$currency->name}}</span></th>
                    @endif
                </tr>
            </tbody>
            <!-- </tfoot> -->
        </table>
        <br>
        <table>
            <tbody>
                @foreach($lims_payment_data as $payment_data)
                <tr style="background-color:#ddd;">
                    <td style="padding: 5px;width:30%">{{trans('file.Paid By')}}: <br>{{$payment_data->paying_method}} </td>
                    <td style="padding: 5px;width:40%">{{trans('file.Amount')}}: <br>{{number_format((float)$payment_data->amount, 0, '.', ' ')}} {{$currency->code}}</td>
                    <td style="padding: 5px;width:30%">{{trans('file.Change')}}: <br>{{number_format((float)$payment_data->change, 0, '.', ' ')}} {{$currency->code}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
                @if ($lims_sale_data->normalization_status == true)
<br>
               <table>
            <tbody>
                    <tr>
                        <td colspan="3" style="text-align:center">
                            {{trans('file.Code MECeF')}} <br>
                            <span class="" style="font-weight: bold">{{$lims_sale_data->normalization_code}}</span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align:left">{{trans('file.MECeF NIM')}}</td>
                        <td style="text-align:right"><span style="font-weight: bold">{{$lims_sale_data->nim_code}}</span></td>
                    </tr>
                    <tr>
                        <td colspan="2" style="text-align:left">{{trans('file.MECeF Counters')}}</td>
                        <td style="text-align:right"><span style="font-weight: bold">{{$lims_sale_data->normalization_counters}}</span></td>
                    </tr>
                    <tr>
                        <td style="text-align:left">{{trans('file.MECeF Hour')}}</td>
                        <td colspan="2" style="text-align:right"><span style="font-weight: bold">{{$lims_sale_data->normalization_date}}</span></td>
                    </tr>
                    <tr>
                        <td class="centered" colspan="3">
                        {{-- <?php //echo '<img style="margin-top:10px;" src="data:image/png;base64,' . DNS1D::getBarcodePNG($lims_sale_data->reference_no, 'C128') . '" width="300" alt="barcode"   />';?>
                        <br> --}}
                        <?php echo '<img style="width:60px; margin-top:10px;" src="data:image/png;base64,' . DNS2D::getBarcodePNG($lims_sale_data->qr_code, 'QRCODE') . '" alt="barcode"   />';?>
                        </td>
                    </tr>
            </tbody>
               </table>
                @endif
                <tr><td class="centered" colspan="3">{{trans('file.Thank you for shopping with us. Please come again')}}</td></tr>
            </tbody>
        </table>
        <!-- <div class="centered" style="margin:30px 0 50px">
            <small>{{trans('file.Invoice Generated By')}} {{$general_setting->site_title}}.
            {{trans('file.Developed By')}} LionCoders</strong></small>
        </div> -->
    </div>
</div>

<script type="text/javascript">
    localStorage.clear();
    function auto_print() {
        window.print()
    }
    setTimeout(auto_print, 1000);
</script>

</body>
</html>
