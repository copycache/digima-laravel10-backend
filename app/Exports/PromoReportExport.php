<?php

namespace App\Exports;

use App\Models\Tbl_slot;
use App\Models\Tbl_dynamic_compression_record;
use App\Models\Tbl_unilevel_points;
use App\Models\Tbl_item;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class PromoReportExport implements FromQuery, WithHeadings, WithTitle, ShouldAutoSize, WithMapping
{
    protected $date;
    protected $level;
    protected $item_id;
    protected $start;
    protected $end;

    public function __construct($date, $level, $item_id)
    {
        $this->date = $date ? $date : Carbon::now();
        $this->level = $level ? $level : 1;
        $this->item_id = $item_id ? $item_id : 0;
        $this->start = Carbon::parse($this->date)->startOfMonth();
        $this->end = Carbon::parse($this->date)->endOfMonth();
    }

    public function query()
    {
        return Tbl_slot::where("membership_inactive", 0)
            ->where("slot_status", "active")
            ->Owner()
            ->select("slot_id", "slot_no", "slot_owner"); // Select necessary fields, Owner() scope joins users
    }

    public function headings(): array
    {
        return [
            'Slot Code',
            'Name',
            'Email',
            'Contact',
            'Level',
            'Item',
            'Qty No.'
        ];
    }

    public function map($slot): array
    {
        $slot_records = Tbl_dynamic_compression_record::where("slot_id", $slot->slot_id)
            ->where("dynamic_level", $this->level)
            ->whereDate("start_date", ">=", $this->start)
            ->whereDate("end_date", "<=", $this->end)
            ->select("cause_slot_id")
            ->get();

        $item_counts = 0;
        $item_sku = "---";

        foreach ($slot_records as $record) {
            $query = Tbl_unilevel_points::where("unilevel_points_slot_id", $record->cause_slot_id)
                ->where("unilevel_points_date_created", ">=", $this->start)
                ->where("unilevel_points_date_created", "<=", $this->end);

            if ($this->item_id == 0) {
                $item_sku = "All";
                $count = $query->count();
            } else {
                $item = Tbl_item::where("tbl_item.archived", 0)->where("item_id", $this->item_id)->select("item_sku")->first();
                $item_sku = $item ? $item->item_sku : "Unknown";
                $count = $query->where("unilevel_item_id", $this->item_id)->count();
            }
            $item_counts += $count;
        }

        // Access user details via relationship or joined fields (Owner scope usually joins users)
        // Assuming Owner scope joins users and selects *, we can access user fields directly on $slot
        // However, standard Eloquent returns model instances.
        // If Owner() scope does a join, attributes are available on $slot.
        // Let's assume standard access: $slot->owner->name etc if relationship exists, or direct access if joined.
        // Based on legacy code: $list['name'] implies joined.
        
        return [
            $slot->slot_no,
            $slot->name,   // Assuming joined by Owner() scope
            $slot->email,  // Assuming joined by Owner() scope
            $slot->contact,// Assuming joined by Owner() scope
            $this->level,
            $item_sku,
            $item_counts
        ];
    }

    public function title(): string
    {
        return 'Promo';
    }
}
