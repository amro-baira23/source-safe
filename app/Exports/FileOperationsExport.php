<?php

namespace App\Exports;

use App\Models\File;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Style;

class FileOperationsExport implements FromCollection, WithHeadings, WithColumnWidths, WithDefaultStyles
{

    public function __construct(private File $file)
    {
        
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $collection = DB::table("locks")
            ->select("users.username as user", "change as edit","locks.created_at as edited_at")
            ->where("file_id",$this->file->id)
            ->join("users","locks.user_id","users.id")
            ->orderBy("locks.created_at","desc")
            ->get();

        return $collection;
    }

    public function headings(): array
    {
        return ["user","edit","edited_at"];
    }

    public function columnWidths(): array
    {
        return [
            "A" => 15,
            "C" => 15
        ];
    }

    public function defaultStyles(Style $defaultStyle)
    {
    
        return [
            'font' => [
                'name' => 'Arial',
                'bold' => true,
                'italic' => false,
                'strikethrough' => false,
                'size' => 10
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ];
    }
}
