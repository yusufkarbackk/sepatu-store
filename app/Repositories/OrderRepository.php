<?php

namespace App\Repositories;

use App\Models\ProductTransaction;
use Illuminate\Support\Facades\Session;

class OrderRepository Implements OrderRepositoryInterface
{
    public function createTransaction(array $data)
    {
        return ProductTransaction::create($data);
    }

    public function findBbyTrxIdAndPhoneNumber($bookingTrxId, $phoneNumber)
    {
        return ProductTransaction::where('booking_trx_id', $bookingTrxId)
            ->where('phone_number', $phoneNumber)
            ->first();
    }

    public function saveToSession(array $data)
    {
        Session::put('order_data', []);
    }

    public function updtateSessionData(array $data)
    {
        $orderData = session('order_data', []);
        $orderData = array_merge($orderData, $data);
        session(['order_data' => $orderData]);
    }

    public function getOrderDataFromSession()
    {
        return session('order_data', []);
    }
}