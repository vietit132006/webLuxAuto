<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TestDrivesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExportTestDrivesRequest;
use App\Http\Requests\Admin\StoreTestDriveFileRequest;
use App\Http\Requests\Admin\StoreTestDriveNoteRequest;
use App\Http\Requests\Admin\TestDriveIndexRequest;
use App\Http\Requests\Admin\UpdateTestDriveAppointmentRequest;
use App\Http\Requests\Admin\UpdateTestDriveStatusRequest;
use App\Models\TestDriveFile;
use App\Models\Ticket;
use App\Services\TestDriveNotificationService;
use App\Services\TestDriveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TestDriveController extends Controller
{
    public function __construct(
        private readonly TestDriveService $testDriveService,
        private readonly TestDriveNotificationService $notificationService
    ) {
    }

    public function index(TestDriveIndexRequest $request): View
    {
        $filters = $request->filters();
        $bookings = $this->testDriveService
            ->query($filters)
            ->paginate(20)
            ->withQueryString();

        return view('admin.test_drives.index', [
            'bookings' => $bookings,
            'filters' => $filters,
            'salesPeople' => $this->testDriveService->salesPeopleOptions(),
            'stats' => $this->testDriveService->stats($filters),
            'statusOptions' => Ticket::testDriveStatusOptions(),
        ]);
    }

    public function show(int $id): View
    {
        $booking = $this->testDriveService->findForShow($id);

        return view('admin.test_drives.show', [
            'booking' => $booking,
            'nextStatusOptions' => $booking->nextTestDriveStatusOptions(),
            'salesPeople' => $this->testDriveService->salesPeopleOptions(),
            'statusOptions' => Ticket::testDriveStatusOptions(),
        ]);
    }

    public function updateStatus(UpdateTestDriveStatusRequest $request, int $id): RedirectResponse
    {
        $booking = $this->testDriveService->findForShow($id);
        [$updatedBooking, $changed, $shouldNotify] = $this->testDriveService->updateStatus(
            $booking,
            $request->validated(),
            $request->user()
        );

        $redirect = back()->with(
            'success',
            $changed ? 'Đã cập nhật trạng thái lịch lái thử.' : 'Trạng thái lịch lái thử không thay đổi.'
        );

        if ($shouldNotify && !$this->notificationService->notifyStatusChanged($updatedBooking)) {
            $redirect->with('warning', 'Trạng thái đã lưu, nhưng email thông báo chưa gửi được. Vui lòng kiểm tra cấu hình mail.');
        }

        return $redirect;
    }

    public function updateAppointment(UpdateTestDriveAppointmentRequest $request, int $id): RedirectResponse
    {
        $booking = $this->testDriveService->findForShow($id);
        $this->testDriveService->updateAppointment($booking, $request->validated(), $request->user());

        return back()->with('success', 'Đã cập nhật thông tin lịch hẹn.');
    }

    public function storeNote(StoreTestDriveNoteRequest $request, int $id): RedirectResponse
    {
        $booking = $this->testDriveService->findForShow($id);
        $this->testDriveService->storeNote($booking, $request->validated('note'), $request->user());

        return back()->with('success', 'Đã thêm ghi chú nội bộ.');
    }

    public function storeFiles(StoreTestDriveFileRequest $request, int $id): RedirectResponse
    {
        $booking = $this->testDriveService->findForShow($id);
        $this->testDriveService->storeFiles($booking, $request->file('documents', []), $request->user());

        return back()->with('success', 'Đã upload tài liệu lái thử.');
    }

    public function viewFile(int $id, TestDriveFile $file): StreamedResponse
    {
        $booking = $this->testDriveService->findForShow($id);
        $this->ensureFileBelongsToBooking($booking, $file);

        return Storage::disk('public')->response($file->file_path, $file->file_name);
    }

    public function downloadFile(int $id, TestDriveFile $file): StreamedResponse
    {
        $booking = $this->testDriveService->findForShow($id);
        $this->ensureFileBelongsToBooking($booking, $file);

        return Storage::disk('public')->download($file->file_path, $file->file_name);
    }

    public function destroyFile(int $id, TestDriveFile $file): RedirectResponse
    {
        $booking = $this->testDriveService->findForShow($id);
        $this->ensureFileBelongsToBooking($booking, $file);
        $this->testDriveService->deleteFile($booking, $file, request()->user());

        return back()->with('success', 'Đã xóa tài liệu lái thử.');
    }

    public function export(ExportTestDrivesRequest $request)
    {
        $filename = 'test_drives_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new TestDrivesExport($this->testDriveService->query($request->filters())),
            $filename
        );
    }

    private function ensureFileBelongsToBooking(Ticket $booking, TestDriveFile $file): void
    {
        abort_unless((int) $file->ticket_id === (int) $booking->ticket_id, 404);
    }
}
