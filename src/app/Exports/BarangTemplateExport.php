<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BarangTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new BarangTemplateDataExport(),
            new BarangInstructionExport(),
        ];
    }
}
