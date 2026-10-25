<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $query = WalletTransaction::query()
            ->with(['project', 'category', 'contractor', 'creator'])
            ->recentFirst();

        if ($type = $request->query('type')) {
            if (in_array($type, array_column(TransactionType::cases(), 'value'), true)) {
                $query->where('type', $type);
            }
        }

        return view('transactions.index', [
            'transactions' => $query->paginate(25)->withQueryString(),
            'selectedType' => $type,
            'types' => TransactionType::cases(),
        ]);
    }
}
