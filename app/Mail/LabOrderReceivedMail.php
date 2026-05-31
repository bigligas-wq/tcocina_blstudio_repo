<?php

namespace App\Mail;

use App\Models\LabOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LabOrderReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public LabOrder $order;

    public function __construct(LabOrder $order)
    {
        $this->order = $order->load('items.improvement', 'user');
    }

    public function build()
    {
        return $this->subject('🚨 Nuevo pedido del Laboratorio — ' . $this->order->order_number)
            ->view('emails.lab-order-received');
    }
}
