<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCustomerApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('q');
        $users = User::query()
            ->where('role', 'customer')
            ->when($search, function ($query, string $search): void {
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('user_id')
            ->paginate(20);

        return response()->json($users);
    }

    public function show(User $user): JsonResponse
    {
        if ($user->role !== 'customer') {
            return response()->json(['message' => 'Not a customer account'], 404);
        }

        $user->load([
            'orders' => fn ($q) => $q->orderByDesc('created_at')->with('details.car:car_id,name,price'),
        ]);

        $orders = $user->orders->map(fn ($order) => [
            'order_id' => $order->order_id,
            'total_price' => (string) $order->total_price,
            'status' => $order->status,
            'created_at' => $order->created_at?->toIso8601String(),
            'lines' => $order->details->map(fn ($d) => [
                'car_id' => $d->car_id,
                'car_name' => $d->car?->name,
                'quantity' => $d->quantity,
                'price' => (string) $d->price,
            ]),
        ]);

        return response()->json([
            'customer' => [
                'user_id' => $user->user_id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
            ],
            'purchase_history' => $orders,
        ]);
    }
}
