<?php

/**
 * StrongShop
 * @author Karen <strongshop@qq.com>
 * @license http://www.strongshop.cn/license/
 * @copyright StrongShop Software
 */

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order\Order;
use App\Models\PaymentOption;

class PayondeliveryController extends Controller
{

    /**
     * [货到付款] 支付页面
     */
    public function index(Request $request)
    {
        $orderId = $request->orderId;
        //订单信息
        $order = Order::query()->where('id', $orderId)
                ->with('orderProducts')
                ->first();
        if (!$order)
        {
            abort(404);
        }
        if (!in_array($order->order_status, [Order::STATUS_UNPAID, Order::STATUS_PAY_FAILED]))
        {
            abort(201, __('Invalid'));
        }

        $model = PaymentOption::where('code', 'paondelivery')->where('status', 1)->first();
        if (!$model)
        {
            abort(201, __('Payment not exists'));
        }

        $cancel_url = route('home');
        //收款账号
        $business = $model->more['business'] ?? config('strongshop.payment.paypal.business');
        //默认设置结算货币
        $defaultCurrencyPay = isset($model->more['currency']) || $model->more['currency'] == null ? $model->more['currency'] : config('strongshop.defaultCurrencyPay');
        //环境
        $env = $model->more['env'] ?? config('strongshop.env');

        $description = config('app.name'); //订单描述
        $currency = $order->currency_code; //订单货币
        $currency_rate = $order->currency_rate; //订单货币费率
        $invoiceNumber = $order->order_no; //订单号
        $total = $order->order_amount; //支付总额

        return view('payment.payondelivery', ['order' => $order, 'payment' => $model]);
    }

}
