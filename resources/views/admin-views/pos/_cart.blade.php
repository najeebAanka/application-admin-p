    <div class="d-flex flex-row" style="max-height: 300px; overflow-y: scroll;">
        <table class="table table-bordered">
            <thead class="text-muted">
                <tr>
                    <th scope="col">{{\App\CPU\translate('item')}}</th>
                    <th scope="col" class="text-center">{{\App\CPU\translate('qty')}}</th>
                    <th scope="col">{{\App\CPU\translate('price')}}</th>
                    <th scope="col">{{\App\CPU\translate('delete')}}</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $subtotal = 0;
                $addon_price = 0;
                $tax = 0;
                $discount = 0;
                $discount_type = 'amount';
                $discount_on_product = 0;
            ?>
            @if(session()->has('cart') && count( session()->get('cart')) > 0)
                <?php
                    $cart = session()->get('cart');
                    if(isset($cart['tax']))
                    {
                        $tax = $cart['tax'];
                    }
                    if(isset($cart['discount']))
                    {
                        $discount = $cart['discount'];
                        $discount_type = $cart['discount_type'];
                    }
                ?>
                @foreach(session()->get('cart') as $key => $cartItem)
                @if(is_array($cartItem))
                    <?php
                    $product_subtotal = ($cartItem['price'])*$cartItem['quantity'];
                    $discount_on_product += ($cartItem['discount']*$cartItem['quantity']);
                    $subtotal += $product_subtotal;
                    $addon_price += $cartItem['addon_price'];
                    ?>
                <tr>
                    <td class="media align-items-center">
                        <img class="avatar avatar-sm mr-1" src="{{asset('storage/product')}}/{{$cartItem['image']}}"
                                onerror="this.src='{{asset('assets/admin/img/160x160/img2.jpg')}}'" alt="{{$cartItem['name']}} image">
                        <div class="media-body">
                            <h5 class="text-hover-primary mb-0">{{Str::limit($cartItem['name'], 10)}}</h5>
                            <small>{{Str::limit($cartItem['variant'], 20)}}</small>
                            <small style="display: block">
                                @php($add_on_qtys=$cartItem['add_on_qtys'])
                                @foreach($cartItem['add_ons'] as $key2 =>$id)
                                    @php($addon=\App\Model\AddOn::find($id))
                                    @if($key2==0)<strong><u>Addons : </u></strong>@endif

                                    @if($add_on_qtys==null)
                                        @php($add_on_qty=1)
                                    @else
                                        @php($add_on_qty=$add_on_qtys[$key2])
                                    @endif

                                    <div class="font-size-sm text-body">
                                        <span>{{$addon['name']}} :  </span>
                                        <span class="font-weight-bold">
                                                {{$add_on_qty}} x {{$addon['price']}} {{\App\CPU\Helpers::currency_symbol()}}
                                            </span>
                                    </div>
                                @endforeach
                            </small>
                        </div>
                    </td>
                    <td class="align-items-center text-center">
                        <input type="number"  data-key="{{$key}}" style="width:50px;text-align: center;" value="{{$cartItem['quantity']}}" min="1" onchange="updateQuantity(event)">
                    </td>
                    <td class="text-center px-0 py-1">
                        <div class="btn">
                            {{$product_subtotal . ' ' . \App\CPU\Helpers::currency_symbol()}}
                        </div> <!-- price-wrap .// -->
                    </td>
                    <td class="align-items-center text-center">
                        <a href="javascript:removeFromCart({{$key}})" class="btn btn-sm btn-outline-danger"> <i class="tio-delete-outlined"></i></a>
                    </td>
                </tr>
                @endif
                @endforeach
            @endif
            </tbody>
        </table>
    </div>

    <?php
        $total = $subtotal+$addon_price;
        $discount_amount = ($discount_type=='percent' && $discount>0)?(($total * $discount)/100):$discount;
        $discount_amount += $discount_on_product;
        $total -= $discount_amount;
        $total_tax_amount= ($tax > 0)?(($total * $tax)/100):0;
    ?>
    <div class="box p-3">
        <dl class="row text-sm-right">

            <dt  class="col-sm-6">{{\App\CPU\translate('addon')}} : </dt>
            <dd class="col-sm-6 text-right">{{\App\CPU\Helpers::currency_converter($addon_price)}}</dd>

            <dt  class="col-sm-6">{{\App\CPU\translate('sub_total')}} : </dt>
            <dd class="col-sm-6 text-right">{{\App\CPU\Helpers::currency_converter($subtotal+$addon_price)}}</dd>


            <dt  class="col-sm-6">{{\App\CPU\translate('product')}} {{\App\CPU\translate('discount')}} :</dt>
            <dd class="col-sm-6 text-right">{{\App\CPU\Helpers::currency_converter(round($discount_amount,2)) }}</dd>

            <dt  class="col-sm-6">{{\App\CPU\translate('extra')}} {{\App\CPU\translate('discount')}} :</dt>
            <dd class="col-sm-6 text-right"><button class="btn btn-sm" type="button" data-toggle="modal" data-target="#add-discount"><i class="tio-edit"></i></button></dd>

            <dt  class="col-sm-6">{{\App\CPU\translate('tax')}} : </dt>
            <dd class="col-sm-6 text-right"><button class="btn btn-sm" type="button" data-toggle="modal" data-target="#add-tax"><i class="tio-edit"></i></button>{{\App\CPU\Helpers::currency_converter(round($total_tax_amount,2))}}</dd>

            <dt  class="col-sm-6">{{\App\CPU\translate('total')}} : </dt>
            <dd class="col-sm-6 text-right h4 b">{{\App\CPU\Helpers::currency_converter(round($total+$total_tax_amount, 2))}}</dd>
        </dl>
        <div class="row">
            <div class="col-md-6">
                <a href="#" class="btn btn-danger btn-sm btn-block" onclick="emptyCart()"><i
                        class="fa fa-times-circle "></i> {{\App\CPU\translate('Cancel')}} </a>
            </div>
            <div class="col-md-6">
                <button type="button" class="btn  btn-primary btn-sm btn-block" data-toggle="modal" data-target="#paymentModal"><i class="fa fa-shopping-bag"></i>
                    {{\App\CPU\translate('Order')}} </button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add-discount" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{\App\CPU\translate('update_discount')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{route('admin.pos.discount')}}" method="post" class="row">
                        @csrf
                        <div class="form-group col-sm-6">
                            <label for="">{{\App\CPU\translate('discount')}}</label>
                            <input type="number" class="form-control" name="discount">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="">{{\App\CPU\translate('type')}}</label>
                            <select name="type" class="form-control">
                                <option value="amount" {{$discount_type=='amount'?'selected':''}}>{{\App\CPU\translate('amount')}}()</option>
                                <option value="percent" {{$discount_type=='percent'?'selected':''}}>{{\App\CPU\translate('percent')}}(%)</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-12">
                            <button class="btn btn-sm btn-primary" type="submit">{{\App\CPU\translate('submit')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add-tax" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{\App\CPU\translate('update_tax')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{route('admin.pos.tax')}}" method="POST" class="row">
                        @csrf
                        <div class="form-group col-12">
                            <label for="">{{\App\CPU\translate('tax')}} (%)</label>
                            <input type="number" class="form-control" name="tax" min="0">
                        </div>

                        <div class="form-group col-sm-12">
                            <button class="btn btn-sm btn-primary" type="submit">{{\App\CPU\translate('submit')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{\App\CPU\translate('payment')}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="{{route('admin.pos.order')}}" id='order_place' method="post" class="row">
                        @csrf
                        <div class="form-group col-12">
                            <label class="input-label" for="">amount(currency symbol)</label>
                            <input type="number" class="form-control" name="amount" min="0" step="0.01" value="{{round($total+$total_tax_amount, 2)}}">
                        </div>
                        <div class="form-group col-12">
                            <label class="input-label" for="">{{\App\CPU\translate('type')}}</label>
                            <select name="type" class="form-control">
                                <option value="cash">{{\App\CPU\translate('cash')}}</option>
                                <option value="card">{{\App\CPU\translate('card')}}</option>
                            </select>
                        </div>
                        <div class="form-group col-12">
                            <button class="btn btn-sm btn-primary" type="submit">{{\App\CPU\translate('submit')}}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

