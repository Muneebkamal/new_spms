<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\Utility;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AdminSearchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   
        $facilities = explode(',', Utility::where('key', 'facilities')->value('value'));
        $types = explode(',', Utility::where('key', 'types')->value('value'));
        $decorations = explode(',', Utility::where('key', 'decorations')->value('value'));
        $usage = explode(',', Utility::where('key', 'usage')->value('value'));
        $districts = explode(',', Utility::where('key', 'district')->value('value'));
        return view('admin-search', compact('facilities', 'types', 'decorations', 'usage', 'districts'));
    }

    // public function search(Request $request)
    // {
    //     $info = $request->info;
    //     $district = $request->district;
    //     $types1 = $request->types1; 
    //     $facilities = $request->facilities;
    //     $types = $request->types;
    //     $decorations = $request->decorations;
    //     $usage = $request->usage;
    //     $options = $request->options;
        
    //     // dd($request->all());
    //     $search = Property::where(function ($query) use ($info) {
    //         $query->where('code', 'LIKE', "%{$info}%")
    //             ->orWhere('building', 'LIKE', "%{$info}%");
    //     })
    //     ->when($district !== 'All', function ($query) use ($district) {
    //         $query->where('district', $district);
    //     })
    //     ->when(is_array($types1) && count($types1), function ($query) use ($types1) {
    //         foreach ($types1 as $type) {
    //             $query->whereRaw("FIND_IN_SET(?, types)", [$type]);
    //         }
    //     })
    //     ->when(is_array($facilities) && count($facilities), function ($query) use ($facilities) {
    //         foreach ($facilities as $facility) {
    //             $query->whereRaw("FIND_IN_SET(?, facilities)", [$facility]);
    //         }
    //     })
    //     ->when(is_array($types) && count($types), function ($query) use ($types) {
    //         foreach ($types as $type) {
    //             $query->whereRaw("FIND_IN_SET(?, types)", [$type]);
    //         }
    //     })
    //     ->when(is_array($decorations) && count($decorations), function ($query) use ($decorations) {
    //         foreach ($decorations as $decoration) {
    //             $query->whereRaw("FIND_IN_SET(?, decorations)", [$decoration]);
    //         }
    //     })
    //     ->when(is_array($usage) && count($usage), function ($query) use ($usage) {
    //         foreach ($usage as $use) {
    //             $query->whereRaw("FIND_IN_SET(?, usage)", [$use]);
    //         }
    //     })
    //     ->when(is_array($options) && count($options), function ($query) use ($options) {
    //         foreach ($options as $option) {
    //             $query->whereJsonContains('others', $option);
    //         }
    //     })
    //     ->when(request('gross_from') && request('gross_to'), function ($query) {
    //         $query->whereBetween('gross_sf', [request('gross_from'), request('gross_to')]);
    //     })
    //     ->when(request('net_from') && request('net_to'), function ($query) {
    //         $query->whereBetween('net_sf', [request('net_from'), request('net_to')]);
    //     })
    //     ->when(request('selling_from') && request('selling_to'), function ($query) {
    //         $query->whereBetween('selling_price', [request('selling_from'), request('selling_to')]);
    //     })
    //     ->when(request('rental_from') && request('rental_to'), function ($query) {
    //         $query->whereBetween('rental_price', [request('rental_from'), request('rental_to')]);
    //     })
    //     ->with('photos')
    //     ->orderBy('created_at', 'desc')
    //     ->get();

    //     $resultCount = $search->count();

    //     return response()->json(['message' => 'Search completed!', 'data' => $search, 'count' => $resultCount]);
    // }

    public function search(Request $request)
    {
        $info = $request->info;
        $district = $request->district;
        $types1 = $request->types1; 
        $facilities = $request->facilities;
        $types = $request->types;
        $decorations = $request->decorations;
        $usage = $request->usage;
        $options = $request->options;

        // Helper for date range
        $getDateRange = function ($filter) {
            if ($filter === '1_week') {
                return [now()->subWeek()->startOfDay(), now()->endOfDay()];
            } elseif ($filter === '2_week') {
                return [now()->subWeeks(2)->startOfDay(), now()->endOfDay()];
            } elseif ($filter === '1_month') {
                return [now()->subMonth()->startOfDay(), now()->endOfDay()];
            } elseif (strpos($filter, ' - ') !== false) {
                [$start, $end] = explode(' - ', $filter);
                return [
                    \Carbon\Carbon::createFromFormat('d-m-Y', trim($start))->startOfDay(),
                    \Carbon\Carbon::createFromFormat('d-m-Y', trim($end))->endOfDay()
                ];
            } else {
                return [
                    \Carbon\Carbon::createFromFormat('d-m-Y', $filter)->startOfDay(),
                    \Carbon\Carbon::createFromFormat('d-m-Y', $filter)->endOfDay()
                ];
            }
        };

        // First build the base query without JSON date filtering
        $search = Property::where(function ($query) use ($info) {
                $query->where('code', 'LIKE', "%{$info}%")
                    ->orWhere('building', 'LIKE', "%{$info}%");
            })
            ->when($district !== 'All', fn($query) => $query->where('district', $district))
            ->when(is_array($types1) && count($types1), function ($query) use ($types1) {
                foreach ($types1 as $type) {
                    $query->whereRaw("FIND_IN_SET(?, types)", [$type]);
                }
            })
            ->when(is_array($facilities) && count($facilities), function ($query) use ($facilities) {
                foreach ($facilities as $facility) {
                    $query->whereRaw("FIND_IN_SET(?, facilities)", [$facility]);
                }
            })
            ->when(is_array($types) && count($types), function ($query) use ($types) {
                foreach ($types as $type) {
                    $query->whereRaw("FIND_IN_SET(?, types)", [$type]);
                }
            })
            ->when(is_array($decorations) && count($decorations), function ($query) use ($decorations) {
                foreach ($decorations as $decoration) {
                    $query->whereRaw("FIND_IN_SET(?, decorations)", [$decoration]);
                }
            })
            ->when(is_array($usage) && count($usage), function ($query) use ($usage) {
                foreach ($usage as $use) {
                    $query->whereRaw("FIND_IN_SET(?, usage)", [$use]);
                }
            })
            ->when(is_array($options) && count($options), function ($query) use ($options) {
                foreach ($options as $option) {
                    $query->whereJsonContains('others', $option);
                }
            })
            ->when(request('gross_from') && request('gross_to'), fn($query) =>
                $query->whereBetween('gross_sf', [request('gross_from'), request('gross_to')])
            )
            ->when(request('net_from') && request('net_to'), fn($query) =>
                $query->whereBetween('net_sf', [request('net_from'), request('net_to')])
            )
            ->when(request('selling_from') && request('selling_to'), fn($query) =>
                $query->whereBetween('selling_price', [request('selling_from'), request('selling_to')])
            )
            ->when(request('rental_from') && request('rental_to'), fn($query) =>
                $query->whereBetween('rental_price', [request('rental_from'), request('rental_to')])
            )
            ->with('photos')
            ->orderBy('created_at', 'desc')
            ->get();

            // Post-filtering for date-linked options
            if (is_array($options) && count($options)) {
                $search = $search->filter(function ($prop) use ($options, $request, $getDateRange) {
                    $others = json_decode($prop->others, true);
                    $dates  = json_decode($prop->other_current_date, true);

                    if (!is_array($others) || !is_array($dates)) {
                        return false;
                    }

                    $matchFound = false;

                    foreach ($options as $option) {
                        if (in_array($option, ['New Released 剛吉', 'Rent Out 巳租'])) {
                            $filterKey = $option === 'New Released 剛吉'
                                ? $request->new_released_date
                                : $request->rent_out_range;

                            // If no date range is given → just check if option exists
                            if (empty($filterKey)) {
                                if (in_array($option, $others, true)) {
                                    $matchFound = true;
                                }
                                continue; // skip date filtering
                            }

                            // If date range is given → filter by date
                            [$fromDate, $toDate] = $getDateRange($filterKey);

                            foreach ($others as $i => $val) {
                                if ($val === $option && isset($dates[$i])) {
                                    try {
                                        $dateVal = \Carbon\Carbon::createFromFormat('Y-m-d', $dates[$i])->startOfDay();
                                    } catch (\Exception $e) {
                                        continue;
                                    }
                                    if ($dateVal->between($fromDate, $toDate)) {
                                        $matchFound = true;
                                    }
                                }
                            }
                        } else {
                            // Non-date options
                            if (in_array($option, $others, true)) {
                                $matchFound = true;
                            }
                        }
                    }

                    return $matchFound;
                });
            }

        return response()->json([
            'message' => 'Search completed!',
            'data' => $search->values(), // reindex
            'count' => $search->count()
        ]);
    }

    public function exportSelectedColumns(Request $request)
    {
        $columnMapping = [
            'code' => 'Code',
            'building' => '大廈',
            'street' => '街道',
            'district' => '地區',
            'floor' => '樓層',
            'flat' => '單位',
            'block' => '座數',
            'rental_price' => '業主叫租',
            'rental_g' => '呎租(建)',
            'rental_n' => '呎租(實)',
            'selling_price' => '售價',
            'selling_g' => '呎價(建)',
            'selling_n' => '呎價(實)',
            'gross_sf' => '建築面積',
            'net_sf' => '實用面積',
            'mgmf' => '管理費',
            'rate' => '差餉',
            'land' => '地租',
            'oths' => '其他',
            'image' => '圖片'
        ];

        $selectedColumns = $request->input('columns', []);
        $selectedIds = $request->input('properties', []);
        $columnsInDb = array_keys($columnMapping);
        $columnsToFetch = array_intersect($selectedColumns, $columnsInDb);

        // Ensure 'code' column is first
        if (in_array('code', $columnsToFetch)) {
            $columnsToFetch = array_merge(['code'], array_diff($columnsToFetch, ['code']));
        }

        // Filter by selected IDs
        $query = Property::query();
        if (!empty($selectedIds)) {
            $query->whereIn('building_id', $selectedIds);
        }

        $data = $query->select(array_diff($columnsToFetch, ['image']))->get();

        // Map the column names in the header to Chinese
        $header = array_map(fn($column) => $columnMapping[$column], $columnsToFetch);

        return Excel::download(new class($data, $header, $columnsToFetch) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithStyles, \Maatwebsite\Excel\Concerns\WithCustomStartCell, \Maatwebsite\Excel\Concerns\WithTitle {
            private $data;
            private $header;
            private $columnsToFetch;

            public function __construct($data, $header, $columnsToFetch)
            {
                $this->data = $data;
                $this->header = $header;
                $this->columnsToFetch = $columnsToFetch;
            }

            public function array(): array
            {
                $rows = $this->data->map(function ($item) {
                    $row = $item->toArray();

                    // Add hyperlink to the image page if image column is selected
                    if (in_array('image', $this->columnsToFetch)) {
                        $pageUrl = route('property.imgs.excel.page', ['code' => $item->code]);
                        $row['image'] = '=HYPERLINK("' . $pageUrl . '", "See Images")';
                    }

                    return $row;
                })->toArray();

                return array_merge([$this->header], $rows);
            }

            public function startCell(): string
            {
                return 'A10'; // Start from row 10
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
            {
                // Insert Image
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Logo');
                $drawing->setDescription('Logo');
                $drawing->setPath(public_path('assets/logos/Picture1.png'));
                $drawing->setHeight(150);
                $drawing->setCoordinates('A1');
                $drawing->setWorksheet($sheet);

                // Footer Style
                $highestColumn = $sheet->getHighestColumn();
                $footerRow = count($this->data) + 10 + 1; // Adjust for the starting row
                $sheet->mergeCells("A{$footerRow}:{$highestColumn}{$footerRow}");
                $sheet->setCellValue("A{$footerRow}", '聲明：有關此物業之介紹書，包括本物業之細則及平面圖僅供參考，本公司巳力求準確，但不擔保或保證他們完整性及正確，貴客戶應自行研究及了解方可作根據。 一切資料並不能構成出價根據或合約中的任何部分。');
                $sheet->getStyle("A{$footerRow}")
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                // Apply font style
                $sheet->getStyle("A1:{$highestColumn}{$footerRow}")
                    ->getFont()
                    ->setName('Calibri')
                    ->setSize(14);
            }

            public function title(): string
            {
                return 'boshinghk-retail';
            }
        }, 'boshinghk-retail.xlsx');
    }

    // public function exportSelectedColumnsPDF(Request $request)
    // {
    //     $columnMapping = [
    //         'code' => 'Code',
    //         'building' => 'Building',
    //         'street' => 'Street',
    //         'district' => 'District',
    //         'floor' => 'Floor',
    //         'flat' => 'Flat',
    //         'block' => 'Block',
    //         'rental_price' => 'Rental Price',
    //         'rental_g' => 'Rental G',
    //         'rental_n' => 'Rental N',
    //         'selling_price' => 'Selling Price',
    //         'selling_g' => 'Selling G',
    //         'selling_n' => 'Selling N',
    //         'gross_sf' => 'Gross SF',
    //         'net_sf' => 'Net SF',
    //         'mgmf' => 'MGMF',
    //         'rate' => 'Rate',
    //         'land' => 'Land',
    //         'oths' => 'Oths'
    //     ];

    //     $selectedColumns = $request->input('columns', []);
    //     $selectedIds = $request->input('properties', []);
    //     $columnsInDb = array_keys($columnMapping);
    //     $columnsToFetch = array_intersect($selectedColumns, $columnsInDb);

    //     if (in_array('code', $columnsToFetch)) {
    //         $columnsToFetch = array_merge(['code'], array_diff($columnsToFetch, ['code']));
    //     }

    //     $query = Property::query();
    //     if (!empty($selectedIds)) {
    //         $query->whereIn('building_id', $selectedIds);
    //     }

    //     $data = $query->select($columnsToFetch)->get();
    //     $header = array_map(fn($col) => $columnMapping[$col], $columnsToFetch);

    //     // $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.properties-export', compact('data', 'header', 'columnsToFetch'))
    //     // ->setPaper('a4', 'landscape');

    //     // return $pdf->download('document.pdf');
    //     $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
    //         'pdf.properties-export', compact('data', 'header', 'columnsToFetch')
    //     )->setPaper('a4', 'landscape')
    //     ->setOptions([
    //         'isHtml5ParserEnabled' => true,
    //         'isRemoteEnabled' => true,
    //         'defaultFont' => 'DejaVu Sans'
    //     ]);

    //     return $pdf->download('document.pdf');
    // }
    public function exportSelectedColumnsPDF(Request $request)
    {
        $columnMapping = [
            'code' => 'Code',
            'building' => 'Building',
            'street' => 'Street',
            'district' => 'District',
            'floor' => 'Floor',
            'flat' => 'Flat',
            'block' => 'Block',
            'rental_price' => 'Rental Price',
            'rental_g' => 'Rental G',
            'rental_n' => 'Rental N',
            'selling_price' => 'Selling Price',
            'selling_g' => 'Selling G',
            'selling_n' => 'Selling N',
            'gross_sf' => 'Gross SF',
            'net_sf' => 'Net SF',
            'mgmf' => 'MGMF',
            'rate' => 'Rate',
            'land' => 'Land',
            'oths' => 'Oths'
        ];

        $selectedColumns = $request->input('columns', []);
        $selectedIds = $request->input('properties', []);
        $columnsInDb = array_keys($columnMapping);
        $columnsToFetch = array_intersect($selectedColumns, $columnsInDb);

        if (in_array('code', $columnsToFetch)) {
            $columnsToFetch = array_merge(['code'], array_diff($columnsToFetch, ['code']));
        }

        $query = Property::query();
        if (!empty($selectedIds)) {
            $query->whereIn('building_id', $selectedIds);
        }

        $data = $query->select($columnsToFetch)->get();

        // Start HTML content
        $html = '<strong style="font-size:15px;">保誠物業代理有限公司</strong>';
        $html .= '<p>Bo Shing Property Agency Limited<br>牌照號碼: (C-088969)</p>';
        $html .= '<p>Date: ' . now()->format('F d, Y') . '</p>';

        $html .= '<table cellspacing="0" cellpadding="5" style="width:100%; border-collapse: collapse;">';

        // Table header
        $html .= '<thead>';
        $html .= '<tr>';
        foreach ($columnsToFetch as $col) {
            $html .= '<th style="
                border-bottom: 2px solid black;  /* bottom border only */
                background-color: #f2f2f2;
                text-align: left;
                padding: 5px;
            ">' . $columnMapping[$col] . '</th>';
        }
        $html .= '</tr>';
        $html .= '</thead>';

        // Table body
        $html .= '<tbody>';
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($columnsToFetch as $col) {
                $html .= '<td style="border: none; padding: 5px;">' . $row->$col . '</td>'; // no border for cells
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        $html .= '</table>';

        $html .= '<p style="text-align:center; font-size:15px; margin-top:20px;">聲明：有關此物業之介紹書，包括本物業之細則及平面圖僅供參考，本公司巳力求準確，但不擔保或保證他們完整性及正確，貴客戶應自行研究及了解方可作根據。 一切資 料並不能構成出價根據或合約中的任何部分。</p>';

        // Configure Chinese font
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'fontDir' => array_merge($fontDirs, [public_path('fonts')]),
            'fontdata' => $fontData + [
                'notosanssc' => [
                    'R' => 'NotoSansSC-Regular.ttf',
                    'B' => 'NotoSansSC-Bold.ttf', // optional bold
                    'I' => 'NotoSansSC-Regular.ttf',
                    'BI' => 'NotoSansSC-Bold.ttf'
                ]
            ],
            'default_font' => 'notosanssc'
        ]);

        $mpdf->WriteHTML($html);

        return $mpdf->Output('document' . now()->format('Y-m-d') . '.pdf', 'D'); // Download
    }

    public function propertyImgsExcel($code)
    {
        $property = Property::where('code', $code)->with('photos')->first();

        return view('shared_pages.excel-photos',compact('property'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
