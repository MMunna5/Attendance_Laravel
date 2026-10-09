<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelReader
{
    public static function rows($file): array
    {
        $reader = IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file->getRealPath());

        return $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
    }
}