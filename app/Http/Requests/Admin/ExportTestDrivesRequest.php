<?php

namespace App\Http\Requests\Admin;

class ExportTestDrivesRequest extends TestDriveIndexRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('test_drives.export');
    }
}
