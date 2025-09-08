<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $q = Order::with(['customer', 'seller'])->latest();

        if ($status = request('status')) {
            $q->where('status', $status);
        }

        $orders = $q->paginate(9);

        return view('dashboard', compact('orders'));
    }


    public function create()
    {
        $sellers = User::select('id', 'name')->orderBy('name')->get();
        $customers = Customer::select('id', 'name')->orderBy('name')->get();
        $products = Product::select('id', 'name', 'price')->orderBy('name')->get();

        return view('livewire.pages.order.create', compact('sellers', 'customers', 'products'));
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'], // kg
        ]);

        $itemsInput = $data['items'];
        $total = 0;

        $productIds = collect($itemsInput)->pluck('product_id')->all();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $order = DB::transaction(function () use ($data, $itemsInput, $products, &$total) {
            $order = Order::create([
                'saller_id' => Auth::user()->id,
                'customer_id' => $data['customer_id'],
                'total_amount' => 0,
                'status' => 'P',
            ]);

            foreach ($itemsInput as $row) {
                $product = $products[$row['product_id']];
                $quantity = (float) $row['quantity']; // kg
                $price = (float) $product->price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                ]);

                $total += $quantity * $price;
            }

            $order->update(['total_amount' => $total]);
            return $order;
        });

        $logController = new LogController();
        $logController->registerLog('Create', 'Order');

        return redirect()->route('orders.payment.create', $order->id)
            ->with('success', 'Pedido criado, prossiga com o pagamento.');

    }

    public function show(Order $order)
    {
        $order->load(['customer', 'seller', 'items.product']);
        return view('livewire.pages.order.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => ['required', 'in:P,C,X,D'],
        ]);

        $order->update([
            'status' => $data['status'],
        ]);

        $logController = new LogController();
        $logController->registerLog('Update Status', 'Order' . $order->id);

        return redirect()->route('orders.show', $order)->with('success', 'Status atualizado com sucesso!');
    }

}
