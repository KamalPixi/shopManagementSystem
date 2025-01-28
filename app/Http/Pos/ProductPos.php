<?php

namespace App\Http\Pos;

use Livewire\Wireable;
use App\Models\Product;

final class ProductPos implements Wireable
{
    protected $product_id;
    protected $quantity = 0;
    protected $price = 0;
    protected $total_price = 0;

    protected $subtotal = 0;
    protected $discount_amount = 0;
    protected $gst_amount = 0;
    protected $final_amount = 0;
    
    protected $customer_name = '';
    protected $customer_phone_no = '';

    public function __construct(
        $product_id,
        $quantity,
        $price,
        $total_price,
        $subtotal,
        $discount_amount,
        $gst_amount,
        $final_amount,
        $customer_name,
        $customer_phone_no,
    )
    {
        $this->product_id = $product_id;
        $this->quantity = $quantity;
        $this->price = $price;
        $this->total_price = $total_price;
        $this->subtotal = $subtotal;
        $this->discount_amount = $discount_amount;
        $this->gst_amount = $gst_amount;
        $this->final_amount = $final_amount;
        $this->customer_name = $customer_name;
        $this->customer_phone_no = $customer_phone_no;
    }


    public function getProduct() : Product {
        return Product::find($this->product_id);
    }

 
    public function toLivewire()
    {
        return [
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'total_price' => $this->total_price,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'gst_amount' => $this->gst_amount,
            'final_amount' => $this->final_amount,
            'customer_name' => $this->customer_name,
            'customer_phone_no' => $this->customer_phone_no,
        ];
    }
 
    public static function fromLivewire($value)
    {
        $product_id = $value['product_id'];
        $quantity = $value['quantity'];
        $price = $value['price'];
        $total_price = $value['total_price'];
        $subtotal = $value['subtotal'];
        $discount_amount = $value['discount_amount'];
        $gst_amount = $value['gst_amount'];
        $final_amount = $value['final_amount'];
        $customer_name = $value['customer_name'];
        $customer_phone_no = $value['customer_phone_no'];
 
        return new static(
            $product_id,
            $quantity,
            $price,
            $total_price,
            $subtotal,
            $discount_amount,
            $gst_amount,
            $final_amount,
            $customer_name,
            $customer_phone_no,
        );
    }
}
