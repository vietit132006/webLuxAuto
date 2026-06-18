<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CarsExport;
use App\Exports\CarsImportTemplateExport;
use App\Exports\InventoryReportExport;
use App\Http\Controllers\Controller;
use App\Imports\CarsImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class CarExcelController extends Controller
{
    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ], [
            'file.required' => 'Vui lòng chọn file Excel để import.',
            'file.file' => 'File import không hợp lệ.',
            'file.mimes' => 'File import phải có định dạng xlsx, xls hoặc csv.',
            'file.max' => 'File import không được vượt quá 10MB.',
        ]);

        $import = new CarsImport();

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->with('error', 'Import Excel chưa thành công. Vui lòng kiểm tra các dòng lỗi bên dưới.');
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Không thể import Excel: ' . $e->getMessage());
        }

        return back()->with('success', "Đã import {$import->importedCount()} xe vào kho.");
    }

    public function export(): BinaryFileResponse
    {
        return Excel::download(
            new CarsExport(),
            'luxauto-danh-sach-xe-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function exportInventory(): BinaryFileResponse
    {
        return Excel::download(
            new InventoryReportExport(),
            'luxauto-bao-cao-ton-kho-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function template(): BinaryFileResponse
    {
        return Excel::download(
            new CarsImportTemplateExport(),
            'luxauto-mau-import-xe.xlsx'
        );
    }
}
