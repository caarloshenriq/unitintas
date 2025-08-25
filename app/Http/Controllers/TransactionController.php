<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    // GET /finance/{type}
    public function index(Request $request, string $type)
    {
        // Filtros via query string
        $status = $request->string('status')->toString(); // pending|paid|cancelled|'' 
        $q      = $request->string('q')->toString();       // descrição
        $from   = $request->date('from');                  // Y-m-d
        $to     = $request->date('to');                    // Y-m-d
        $sort   = in_array($request->string('sort')->toString(), ['due_date','amount','created_at'])
                    ? $request->string('sort')->toString()
                    : 'due_date';
        $dir    = $request->string('dir')->toString() === 'desc' ? 'desc' : 'asc';

        $query = Transaction::query()->where('type', $type);

        if ($status) {
            $query->where('status', $status);
        }
        if ($q) {
            $query->where('description', 'like', "%{$q}%");
        }
        if ($from) {
            $query->whereDate('due_date', '>=', $from->toDateString());
        }
        if ($to) {
            $query->whereDate('due_date', '<=', $to->toDateString());
        }

        $transactions = $query->orderBy($sort, $dir)->paginate(12)->withQueryString();

        // KPIs (recalculados com os filtros aplicados)
        $base = clone $query;
        $sumPending = (clone $base)->where('status', 'pending')->sum('amount');
        $sumPaid    = (clone $base)->where('status', 'paid')->sum('amount');
        $sumCancel  = (clone $base)->where('status', 'cancelled')->sum('amount');

        return view('livewire.pages.transactions', compact(
            'transactions','type','sumPending','sumPaid','sumCancel','status','q','from','to','sort','dir'
        ));
    }

    // POST /finance
    public function store(Request $request)
    {
        $data = $request->validate([
            'type'        => ['required','in:receivable,payable'],
            'description' => ['required','string','max:255'],
            'amount'      => ['required','numeric','min:0.01'],
            'due_date'    => ['required','date'],
        ]);

        Transaction::create($data + ['status' => 'pending', 'order_id' => null]);

        return redirect()
            ->route('finance.index', ['type' => $data['type']])
            ->with('success', 'Transação criada.');
    }

    // PATCH /transactions/{transaction}/status
    public function updateStatus(Request $request, Transaction $transaction)
    {
        $data = $request->validate([
            'status' => ['required','in:pending,paid,cancelled'],
        ]);

        $transaction->update(['status' => $data['status']]);

        return back()->with('success', 'Status atualizado.');
    }

    // DELETE /transactions/{transaction}
    public function destroy(Transaction $transaction)
    {
        $type = $transaction->type;
        $transaction->delete();

        return redirect()->route('finance.index', ['type' => $type])
            ->with('success', 'Transação excluída.');
    }
}
