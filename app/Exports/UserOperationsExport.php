<?php

namespace App\Exports;

use App\Models\Lock;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Style;

class UserOperationsExport implements FromCollection, WithHeadings, WithColumnWidths, WithDefaultStyles
{

    public function __construct(private User $user)
    {
        
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $collection = $collection = Lock::query()
            ->select("files.name as file", "locks.change","locks.created_at as date")
            ->where("user_id",request()->user()->id)
            ->whereHas("file",function ($query) {
                return $query->where("group_id",request("group")->id);
            })
            ->join("files","locks.file_id","files.id")
            ->orderBy("locks.created_at","desc")
            ->get(); 

        return $collection;
    }

    public function headings(): array
    {
        return ["file","edit","edited_at"];
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
