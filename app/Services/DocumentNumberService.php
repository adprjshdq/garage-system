<?php

namespace App\Services;

use App\Models\DocumentSequence;
use App\Models\WorkshopSetting;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /**
     * Generate unique sequential document number with concurrency lock.
     */
    public function generate(string $type): string
    {
        $year = (int) date('Y');
        $month = (int) date('m');
        $yearMonth = date('Ym');

        return DB::transaction(function () use ($type, $year, $month, $yearMonth) {
            $prefix = $this->getPrefix($type);

            $sequence = DocumentSequence::where('document_type', $type)
                ->where('year', $year)
                ->where('month', $month)
                ->lockForUpdate()
                ->first();

            if (! $sequence) {
                $sequence = DocumentSequence::create([
                    'document_type' => $type,
                    'prefix' => $prefix,
                    'current_sequence' => 0,
                    'year' => $year,
                    'month' => $month,
                ]);
            }

            $sequence->current_sequence += 1;
            $sequence->save();

            $padLength = 4;
            $number = str_pad((string) $sequence->current_sequence, $padLength, '0', STR_PAD_LEFT);

            // Document sequence formatting
            return match ($type) {
                'CUST', 'MECH', 'PART', 'SUPP' => "{$prefix}-{$number}",
                default => "{$prefix}/{$yearMonth}/{$number}",
            };
        });
    }

    private function getPrefix(string $type): string
    {
        return match ($type) {
            'WO' => WorkshopSetting::get('prefix_wo', 'WO'),
            'INV' => WorkshopSetting::get('prefix_invoice', 'INV'),
            'REC' => WorkshopSetting::get('prefix_reception', 'REC'),
            'PO' => WorkshopSetting::get('prefix_po', 'PO'),
            'GRN' => 'GRN',
            'RET' => 'RET',
            'PAY' => 'PAY',
            'EXP' => 'EXP',
            'CUST' => 'CUST',
            'MECH' => 'MECH',
            'PART' => 'PART',
            'SUPP' => 'SUPP',
            default => $type,
        };
    }
}
