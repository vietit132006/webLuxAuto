<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestDriveController extends Controller
{
    private const STATUSES = ['pending', 'approved', 'rejected', 'completed'];

    public function index(Request $request): View
    {
        $status = $request->query('status');
        if ($status !== null && !in_array($status, self::STATUSES, true)) {
            $status = null;
        }

        $bookings = Ticket::query()
            ->where('ticket_type', 'test_drive')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->with(['user', 'car.brand'])
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'rejected', 'completed')")
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.test_drives.index', compact('bookings', 'status'));
    }

    public function show(int $id): View
    {
        $booking = Ticket::query()
            ->where('ticket_type', 'test_drive')
            ->with(['user', 'car.brand'])
            ->findOrFail($id);

        return view('admin.test_drives.show', compact('booking'));
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|string|in:' . implode(',', self::STATUSES),
        ]);

        $booking = Ticket::query()
            ->where('ticket_type', 'test_drive')
            ->findOrFail($id);

        if ($booking->status === 'completed') {
            return back()->withErrors(['status' => 'Lịch lái thử đã hoàn tất, không thể cập nhật.']);
        }

        $next = $data['status'];
        if (!$this->isValidTransition((string) $booking->status, $next)) {
            return back()->withErrors(['status' => 'Chuyển trạng thái không hợp lệ.']);
        }

        $booking->update(['status' => $next]);

        return back()->with('success', 'Đã cập nhật trạng thái lịch lái thử.');
    }

    private function isValidTransition(string $from, string $to): bool
    {
        if ($from === $to) {
            return true;
        }

        $allowed = [
            'pending' => ['approved', 'rejected'],
            'approved' => ['completed', 'rejected'],
            'rejected' => [],
            'completed' => [],
        ];

        // Nếu dữ liệu cũ không theo chuẩn, cho phép admin đưa về pending
        if (!array_key_exists($from, $allowed)) {
            return $to === 'pending';
        }

        return in_array($to, $allowed[$from], true);
    }
}

