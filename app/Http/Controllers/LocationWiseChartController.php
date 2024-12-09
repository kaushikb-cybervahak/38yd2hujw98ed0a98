<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Statuslabel;
use App\Models\Location;
use App\Models\Asset;
use Carbon\Carbon;


class LocationWiseChartController extends Controller
{
    public function index(){
        $locations = Location::all();
        return view('location_wise_chart', compact('locations'));
    }
    
    public function getLocationWiseGraph(Request $request){
        $location = Location::find($request->location_id);
        $asset_labelling_status = Asset::where('location_id', $location->id)
                                        ->select('_snipeit_labelling_status_19', DB::raw('COUNT(*) as count'))
                                        ->groupBy('_snipeit_labelling_status_19')
                                        ->pluck('count', '_snipeit_labelling_status_19')
                                        ->toArray();


        $asset_labelling_status_data = [
            'labels' => array_keys($asset_labelling_status),
            'datasets' => [
                [
                    'label' => 'Quantity',
                    'backgroundColor' => ['#ec12e9', '#c1de08', '#ec1212', '#1230ec', '#00b31e'],
                    'data' => array_values($asset_labelling_status)
                ]
            ]
        ];

        //Asset Status data
        $statuslabelIds = Asset::where('location_id', $location->id)->pluck('status_id')->toArray();
        $statuslabels = Statuslabel::whereIn('id', $statuslabelIds)->withCount('assets')->get();
        $total = [];
        $colours = [];
        $ids = []; // Array to store status IDs

        foreach ($statuslabels as $statuslabel) {
            $total[$statuslabel->name] = $statuslabel->assets_count;
            array_push($colours, $statuslabel->color);
            $ids[] = $statuslabel->id; // Collect the status IDs
        }

        $asset_status_data = [
            'labels' => array_keys($total),
            'datasets' => [
                [
                    'label' => 'Quantity',
                    'backgroundColor' => $colours,
                    'data' => array_values($total)
                ]
            ],
            'ids' => $ids // Include the status IDs
        ];

        $asset_category = Asset::where('location_id', $location->id)
                        ->join('models', 'assets.model_id', '=', 'models.id') // Join assets with models table
                        ->join('categories', 'models.category_id', '=', 'categories.id') // Join models with categories table
                        ->select('categories.id as category_id', 'categories.name as category_name', DB::raw('COUNT(assets.id) as count')) // Include category_id
                        ->groupBy('categories.id', 'categories.name') // Group by category_id and category_name
                        ->get();

        $asset_category_data = [
            'labels' => $asset_category->pluck('category_name')->toArray(),
            'datasets' => [
                [
                    'label' => 'Quantity',
                    'backgroundColor' => ['#1230ec', '#ec1212', '#fff00f', '#ec12e9', '#00b31e'],
                    'data' => $asset_category->pluck('count')->toArray(),
                ]
            ],
            'ids' => $asset_category->pluck('category_id')->toArray()
        ];

        $now = Carbon::now();
        $results = DB::table('assets')
            ->where('location_id', $location->id)
            ->selectRaw("
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, purchase_date, DATE_ADD(purchase_date, INTERVAL warranty_months MONTH)) <= 1 THEN 1 ELSE 0 END) AS less_than_1_year,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, purchase_date, DATE_ADD(purchase_date, INTERVAL warranty_months MONTH)) > 1 AND TIMESTAMPDIFF(YEAR, purchase_date, DATE_ADD(purchase_date, INTERVAL warranty_months MONTH)) <= 3 THEN 1 ELSE 0 END) AS between_1_and_3_years,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, purchase_date, DATE_ADD(purchase_date, INTERVAL warranty_months MONTH)) > 3 AND TIMESTAMPDIFF(YEAR, purchase_date, DATE_ADD(purchase_date, INTERVAL warranty_months MONTH)) <= 5 THEN 1 ELSE 0 END) AS between_3_and_5_years,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, purchase_date, DATE_ADD(purchase_date, INTERVAL warranty_months MONTH)) > 5 THEN 1 ELSE 0 END) AS more_than_5_years
            ")->first();

        $array = [
            $results->less_than_1_year,
            $results->between_1_and_3_years,
            $results->between_3_and_5_years,
            $results->more_than_5_years,
        ];

        $warranty_status_data = [
            'labels'=> ['0-1 year', '1-3 years', '3-5 years', '5+ years'],
            'datasets'=> [
                [
                    'label'=> 'Quantity',
                    'backgroundColor'=> ['#ff6384', '#36a2eb', '#cc65fe', '#ffce56'],
                    'borderColor'=> '#ccc',
                    'borderWidth'=> 1,
                    'data'=> $array
                ]
            ]
        ];

        $manufacturers = Asset::where('location_id', $location->id)
                ->join('models', 'assets.model_id', '=', 'models.id') // Join assets with models table
                ->join('manufacturers', 'models.manufacturer_id', '=', 'manufacturers.id') // Join models with manufacturers table
                ->select(
                    'manufacturers.id as manufacturer_id', 
                    'manufacturers.name as manufacturer_name', 
                    DB::raw('COUNT(assets.id) as count')
                )
                ->groupBy('manufacturers.id', 'manufacturers.name') // Group by manufacturer id and name
                ->get();

        $labels = $manufacturers->pluck('manufacturer_name')->toArray(); // Manufacturer names
        $data = $manufacturers->pluck('count')->toArray(); // Counts
        $ids = $manufacturers->pluck('manufacturer_id')->toArray(); // Manufacturer IDs

        $manufacturers_data = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Quantity',
                    'backgroundColor' => ['#1230ec', '#ec1212', '#fff00f', '#ec12e9', '#00b31e'],
                    'data' => $data
                ]
            ],
            'ids' => $ids // Include manufacturer IDs
        ];

        $response = [
            'asset_labelling_status_data' => $asset_labelling_status_data,
            'asset_status_data' => $asset_status_data,
            'asset_category_data' => $asset_category_data,
            'warranty_status_data' => $warranty_status_data,
            'manufacturers_data' => $manufacturers_data,
        ];
        return $response;
    }
}
