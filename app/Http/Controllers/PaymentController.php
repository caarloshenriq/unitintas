<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function create(Order $order)
    {
        $order->load(['customer','items.product']);
        return view('livewire.pages.order.payments', compact('order'));
    }

    public function store(Request $request, Order $order)
    {
        $request->validate([
            'discount'        => ['nullable','numeric','min:0'],
            'payment_method'  => ['required','in:debito,credito,pix,dinheiro'],
            'installments'    => ['nullable','integer','min:1','max:24'],
            'first_due_date'  => ['nullable','date'],
        ]);

        $discount       = (float)($request->input('discount', 0));
        $method         = $request->input('payment_method');
        $installments   = (int)($request->input('installments', 1));
        $firstDue       = $request->input('first_due_date')
                                ? Carbon::parse($request->input('first_due_date'))
                                : now();

        // Se não for crédito, força 1 parcela
        if ($method !== 'credito') {
            $installments = 1;
        }

        // Total líquido (não deixa negativo)
        $total = max(0, (float)$order->total_amount - $discount);

        // Calcula parcelas iguais, ajustando centavos na última
        $per = $installments ? floor(($total / $installments) * 100) / 100 : $total;
        $soma = $per * $installments;
        $ajuste = round(($total - $soma), 2); // centavos que sobraram

        DB::transaction(function () use ($order, $method, $installments, $firstDue, $per, $ajuste, $total, $discount) {
            // Gera payments + transactions (recebíveis)
            for ($i=1; $i <= $installments; $i++) {
                $valor = $per;
                if ($i === $installments) {
                    $valor = round($valor + $ajuste, 2);
                }
                $due = (clone $firstDue)->addMonthsNoOverflow($i - 1);

                Payment::create([
                    'order_id'       => $order->id,
                    'amount'         => $valor,
                    'payment_method' => $method,
                    'status'         => 'pending',
                ]);

                Transaction::create([
                    'type'        => 'receivable',
                    'description' => "Pedido #{$order->id} - Parcela {$i}/{$installments}" . ($discount>0 && $i===1 ? " (c/ desconto)" : ""),
                    'amount'      => $valor,
                    'due_date'    => $due->toDateString(),
                    'status'      => 'pending',
                    'order_id'    => $order->id,
                ]);
            }
        });

        return redirect()->route('orders.show', $order->id)
            ->with('success', "Pagamento gerado em {$installments} parcela(s). Total R$ " . number_format($total,2,',','.'));
    }
}
