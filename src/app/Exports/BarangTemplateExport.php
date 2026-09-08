<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BarangTemplateExport implements Export, WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new BarangTemplateDataExport(),
            new BarangInstructionExport(),
        ];
    }
}
