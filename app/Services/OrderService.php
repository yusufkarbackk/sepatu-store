<?php

namespace App\Services;

use App\Models\ProductTransaction;
use App\Models\PromoCode;
use App\Repositories\CategoryRepository;
use App\Repositories\Contracts\ShoeRepositoryInterface;
use App\Repositories\OrderRepositoryInterface;
use App\Repositories\PromoCodeRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    protected $orderRepository;
    protected $promoCodeRepository;
    protected $categoryRepository;
    protected $shoeRepository;

    public function __construct(
        PromoCodeRepository $promoCodeRepository,
        CategoryRepository $categoryRepository,
        ShoeRepositoryInterface $shoeRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->promoCodeRepository = $promoCodeRepository;
        $this->categoryRepository = $categoryRepository;
        $this->shoeRepository = $shoeRepository;
        $this->orderRepository = $orderRepository;
    }

    public function beginOrder(array $data)
    {
        $orderData = [
            'shoe_size' => $data['shoe_size'],
            'size_id' => $data['size_id'],
            'shoe_id' => $data['shoe_id'],
        ];

        $this->orderRepository->saveToSession($orderData);
    }

    public function getOrderDetails()
    {
        $orderData = $this->orderRepository->getOrderDataFromSession();
        $shoe = $this->shoeRepository->find($orderData['shoe_id']);

        $quantity = isset($orderData['quantity']) ? $orderData['quantity'] : 1;
        $subTotalAmount = $shoe->price * $quantity;

        $taxRate = 0.11;
        $totalTax = $subTotalAmount * $taxRate;

        $grandTotalAmount = $subTotalAmount + $totalTax;

        $orderData['sub_total_amount'] = $subTotalAmount;
        $orderData['total_tax'] = $totalTax;
        $orderData['grand_total_amount'] = $grandTotalAmount;

        return compact('orderData', 'shoe');
    }

    public function applyPromoCode(string $code, int $subTotalAmount)
    {
        $promo = $this->promoCodeRepository->findByCode($code);
        if ($promo) {
            $discount = $promo->discount_amount;
            $grandTotalAmount = $subTotalAmount - $discount;
            $promoCodeId = $promo->id;
            return [
                'discount' => $discount,
                'grand_total_amount' => $grandTotalAmount,
                'promo_code_id' => $promoCodeId
            ];
        }

        return ['error' => 'Invalid promo code'];
    }

    public function saveBookingTransaction(array $data)
    {
        $this->orderRepository->saveToSession($data);
    }

    public function updateCustomerData(array $data)
    {
        $this->orderRepository->updtateSessionData($data);
    }

    public function paymentConfirm(array $validated)
    {
        $orderData = $this->orderRepository->getOrderDataFromSession();
        $productTransactionId = null;

        try { //closure based transaction
            DB::transaction(function () use ($validated, $orderData, &$productTransactionId) {
                if (isset($validated['proof'])) {
                    $proofPath = $validated['proof']->store('proofs', 'public');
                    $validated['proof'] = $proofPath;
                }

                $validated['name'] = $orderData['name'];
                $validated['email'] = $orderData['email'];
                $validated['phone'] = $orderData['phone'];
                $validated['address'] = $orderData['address'];
                $validated['post_code'] = $orderData['post_code'];
                $validated['city'] = $orderData['city'];
                $validated['quantity'] = $orderData['quantity'];
                $validated['sub_total_amount'] = $orderData['sub_total_amount'];
                $validated['grand_total_amount'] = $orderData['grand_total_amount'];
                $validated['discount_amount'] = $orderData['discount_amount'];
                $validated['promo_code_id'] = $orderData['promo_code_id'];
                $validated['shoe_id'] = $orderData['shoe_id'];
                $validated['shoe_size_id'] = $orderData['shoe_size_id'];
                $validated['is_paid'] = false;
                $validated['booking_trx_id'] = ProductTransaction::generateUniqueTrxId();

                $newTransaction = $this->orderRepository->createTransaction($validated);
                $productTransactionId = $newTransaction->id;
            });
        } catch (\Throwable $th) {
            Log::error('Payment confirmation failed: ' . $th->getMessage());
            session()->flash('error', $th->getMessage());
            return null;
        }
        return $productTransactionId;
    }
}
