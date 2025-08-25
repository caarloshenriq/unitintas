<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Customer;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        // Período padrão = mês atual
        $request->validate([
            'from' => ['nullable','date'],
            'to'   => ['nullable','date','after_or_equal:from'],
            'top'  => ['nullable','integer','min:3','max:50'],
        ]);

        $from = $request->filled('from') ? Carbon::parse($request->input('from'))->startOfDay()
                                         : now()->startOfMonth();
        $to   = $request->filled('to')   ? Carbon::parse($request->input('to'))->endOfDay()
                                         : now()->endOfMonth();
        $topN = (int)($request->input('top', 10));

        // labels diários do período
        $period = CarbonPeriod::create($from->copy()->startOfDay(), $to->copy()->startOfDay());
        $labels = [];
        foreach ($period as $date) { $labels[] = $date->format('d/m'); }

        // -------------------------------
        // 1) Entradas x Saídas (transactions)
        // -------------------------------
        $trx = Transaction::query()
            ->selectRaw('DATE(due_date) as d, type, SUM(amount) as total')
            ->whereBetween('due_date', [$from, $to])
            ->whereIn('status', ['pending','paid']) // exclui canceladas; ajuste se quiser
            ->groupBy('d','type')
            ->orderBy('d')
            ->get();

        // mapear por dia
        $inByDay  = array_fill(0, count($labels), 0.0);
        $outByDay = array_fill(0, count($labels), 0.0);

        $indexByLabel = [];
        foreach ($labels as $i => $lbl) {
            // lbl = d/m; cria chave yyyy-mm-dd pra lookup
            $key = Carbon::createFromFormat('d/m', $lbl)->year(now()->year)->format('m-d'); // ano corrente
            $indexByLabel[$i] = $key;
        }
        // lookup por data real (mais seguro)
        $posByDate = [];
        $cursor = $from->copy();
        for ($i=0; $i<count($labels); $i++) {
            $posByDate[$cursor->toDateString()] = $i;
            $cursor->addDay();
        }
        foreach ($trx as $row) {
            $pos = $posByDate[$row->d] ?? null;
            if ($pos === null) continue;
            if ($row->type === 'receivable')  $inByDay[$pos]  += (float)$row->total;
            if ($row->type === 'payable')     $outByDay[$pos] += (float)$row->total;
        }

        // -------------------------------
        // 2) Pedidos por dia (count e faturamento)
        // -------------------------------
        $ordersAgg = Order::query()
            ->selectRaw('DATE(created_at) as d, COUNT(*) as q, SUM(total_amount) as t')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        $ordersCountByDay = array_fill(0, count($labels), 0);
        $ordersTotalByDay = array_fill(0, count($labels), 0.0);
        foreach ($ordersAgg as $o) {
            $pos = $posByDate[$o->d] ?? null;
            if ($pos === null) continue;
            $ordersCountByDay[$pos] = (int)$o->q;
            $ordersTotalByDay[$pos] = (float)$o->t;
        }

        // -------------------------------
        // 3) Pedidos por cliente (TOP N)
        // -------------------------------
        $byCustomer = Order::query()
            ->select('customer_id', DB::raw('COUNT(*) as qtd'), DB::raw('SUM(total_amount) as total'))
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('customer_id')
            ->orderByDesc('total')
            ->limit($topN)
            ->get()
            ->map(function($r){
                $r->customer_name = optional($r->customer)->name ?? "—";
                return $r;
            });

        // -------------------------------
        // 4) Produtos mais vendidos
        // -------------------------------
        $bestProducts = OrderItem::query()
            ->select('product_id', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(quantity * price) as total'))
            ->whereHas('order', function($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from, $to]);
            })
            ->groupBy('product_id')
            ->orderByDesc('qty')
            ->limit($topN)
            ->get()
            ->map(function($r){
                $r->product_name = optional($r->product)->name ?? "—";
                return $r;
            });

        // -------------------------------
        // 5) Vendas por usuário (vendedor)
        // -------------------------------
        $byUser = Order::query()
            ->select('saller_id', DB::raw('COUNT(*) as qtd'), DB::raw('SUM(total_amount) as total'))
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('saller_id')
            ->orderByDesc('total')
            ->get()
            ->map(function($r){
                $r->user_name = optional($r->seller)->name ?? '—';
                return $r;
            });

        // datasets p/ Chart.js
        $chart = [
            'labels' => $labels,
            'in'     => $inByDay,
            'out'    => $outByDay,
            'orders_count' => $ordersCountByDay,
            'orders_total' => $ordersTotalByDay,
            'by_customer'  => [
                'labels' => $byCustomer->pluck('customer_name'),
                'total'  => $byCustomer->pluck('total')->map(fn($v)=>(float)$v),
            ],
            'best_products' => [
                'labels' => $bestProducts->pluck('product_name'),
                'qty'    => $bestProducts->pluck('qty')->map(fn($v)=>(float)$v),
            ],
            'by_user' => [
                'labels' => $byUser->pluck('user_name'),
                'total'  => $byUser->pluck('total')->map(fn($v)=>(float)$v),
            ],
        ];

        return view('livewire.pages.reports', [
            'from' => $from, 'to' => $to, 'topN' => $topN, 'chart' => $chart,
        ]);
    }

    // --- EXPORTS OPCIONAIS ---

    public function exportTransactions(Request $request): StreamedResponse
    {
        $from = $request->filled('from') ? Carbon::parse($request->input('from'))->startOfDay()
                                         : now()->startOfMonth();
        $to   = $request->filled('to')   ? Carbon::parse($request->input('to'))->endOfDay()
                                         : now()->endOfMonth();

        $rows = Transaction::whereBetween('due_date', [$from, $to])
            ->orderBy('due_date')->get(['type','description','amount','due_date','status','order_id']);

        return $this->csvResponse('transactions.csv', ['type','description','amount','due_date','status','order_id'], $rows);
    }

    public function exportOrders(Request $request): StreamedResponse
    {
        $from = $request->filled('from') ? Carbon::parse($request->input('from'))->startOfDay()
                                         : now()->startOfMonth();
        $to   = $request->filled('to')   ? Carbon::parse($request->input('to'))->endOfDay()
                                         : now()->endOfMonth();

        $rows = Order::whereBetween('created_at', [$from, $to])
            ->orderBy('created_at')->get(['id','customer_id','saller_id','total_amount','status','created_at']);

        return $this->csvResponse('orders.csv', ['id','customer_id','saller_id','total_amount','status','created_at'], $rows);
    }

    private function csvResponse(string $filename, array $headers, $rows): StreamedResponse
    {
        return response()->streamDownload(function() use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers, ';');
            foreach ($rows as $r) {
                fputcsv($out, collect($headers)->map(fn($h)=>$r->{$h})->all(), ';');
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
