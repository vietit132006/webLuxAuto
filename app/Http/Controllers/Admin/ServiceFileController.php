<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceAppointment;
use App\Models\ServiceFile;
use App\Models\ServiceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceFileController extends Controller
{
    public function storeForAppointment(Request $request, ServiceAppointment $serviceAppointment)
    {
        $this->validatedFiles($request);

        foreach ($request->file('service_files', []) as $file) {
            $serviceAppointment->files()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $file->store('service-files/appointments/' . $serviceAppointment->id, 'public'),
                'uploaded_by' => $request->user()?->getKey(),
            ]);
        }

        return back()->with('success', 'Đã tải lên tài liệu dịch vụ.');
    }

    public function storeForRecord(Request $request, ServiceRecord $serviceRecord)
    {
        $this->validatedFiles($request);

        foreach ($request->file('service_files', []) as $file) {
            $serviceRecord->files()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $file->store('service-files/records/' . $serviceRecord->id, 'public'),
                'uploaded_by' => $request->user()?->getKey(),
            ]);
        }

        return back()->with('success', 'Đã tải lên tài liệu dịch vụ.');
    }

    public function view(ServiceFile $serviceFile)
    {
        abort_unless(Storage::disk('public')->exists($serviceFile->file_path), 404);

        return Storage::disk('public')->response(
            $serviceFile->file_path,
            $serviceFile->file_name
        );
    }

    public function download(ServiceFile $serviceFile)
    {
        abort_unless(Storage::disk('public')->exists($serviceFile->file_path), 404);

        return Storage::disk('public')->download(
            $serviceFile->file_path,
            $serviceFile->file_name
        );
    }

    public function destroy(ServiceFile $serviceFile)
    {
        Storage::disk('public')->delete($serviceFile->file_path);
        $serviceFile->delete();

        return back()->with('success', 'Đã xóa tài liệu dịch vụ.');
    }

    private function validatedFiles(Request $request): void
    {
        $request->validate([
            'service_files' => 'required|array|max:10',
            'service_files.*' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
        ]);
    }
}
