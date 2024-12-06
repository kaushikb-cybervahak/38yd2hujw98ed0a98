<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\RedirectResponse;
use \Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use App\Models\Asset;
use Carbon\Carbon;




/**
 * This controller handles all actions related to the Admin Dashboard
 * for the Snipe-IT Asset Management application.
 *
 * @author A. Gianotto <snipe@snipe.net>
 * @version v1.0
 */
class DashboardController extends Controller
{
    /**
     * Check authorization and display admin dashboard, otherwise display
     * the user's checked-out assets.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     */
    public function index() : View | RedirectResponse
    {
        // Show the page
        if (auth()->user()->hasAccess('admin')) {
            $asset_stats = null;

            $counts['asset'] = \App\Models\Asset::count();
            $counts['accessory'] = \App\Models\Accessory::count();
            $counts['license'] = \App\Models\License::assetcount();
            $counts['consumable'] = \App\Models\Consumable::count();
            $counts['component'] = \App\Models\Component::count();
            $counts['user'] = \App\Models\Company::scopeCompanyables(auth()->user())->count();
            $counts['grand_total'] = $counts['asset'] + $counts['accessory'] + $counts['license'] + $counts['consumable'];

            if ((! file_exists(storage_path().'/oauth-private.key')) || (! file_exists(storage_path().'/oauth-public.key'))) {
                Artisan::call('migrate', ['--force' => true]);
                \Artisan::call('passport:install');
            }

            return view('dashboard')->with('asset_stats', $asset_stats)->with('counts', $counts);
        } else {
            Session::reflash();

            // Redirect to the profile page
            return redirect()->intended('account/view-assets');
        }
    }

    public function getHardwareAssetWarranty(){
        $now = Carbon::now();

        $results = DB::table('assets')
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

        $data = [
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
        return $data;
    }

    public function getHardwareAssetAge(){
        $now = Carbon::now();

        $results = DB::table('assets')
        ->selectRaw("
            SUM(CASE WHEN TIMESTAMPDIFF(YEAR, purchase_date, ?) <= 1 THEN 1 ELSE 0 END) AS less_than_1_year,
            SUM(CASE WHEN TIMESTAMPDIFF(YEAR, purchase_date, ?) > 1 AND TIMESTAMPDIFF(YEAR, purchase_date, ?) <= 2 THEN 1 ELSE 0 END) AS between_1_and_2_years,
            SUM(CASE WHEN TIMESTAMPDIFF(YEAR, purchase_date, ?) > 2 AND TIMESTAMPDIFF(YEAR, purchase_date, ?) <= 3 THEN 1 ELSE 0 END) AS between_2_and_3_years,
            SUM(CASE WHEN TIMESTAMPDIFF(YEAR, purchase_date, ?) > 3 AND TIMESTAMPDIFF(YEAR, purchase_date, ?) <= 4 THEN 1 ELSE 0 END) AS between_3_and_4_years,
            SUM(CASE WHEN TIMESTAMPDIFF(YEAR, purchase_date, ?) > 4 THEN 1 ELSE 0 END) AS more_than_4_years
        ", [$now, $now, $now, $now, $now, $now, $now, $now])
        ->first();

        $array = [
            $results->less_than_1_year,
            $results->between_1_and_2_years,
            $results->between_2_and_3_years,
            $results->between_3_and_4_years,
            $results->more_than_4_years,
        ];

        $data = [
            'labels'=> ['0-1 year', '1-2 years', '2-3 years', '3-4 years', '4+ years'],
            'datasets'=> [
                [
                    'label'=> 'Quantity',
                    'backgroundColor'=> ['#ec12e9', '#00b31e', '#ec1212', '#1230ec', '#c1de08'],
                    'borderColor'=> '#ccc',
                    'borderWidth'=> 1,
                    'data'=> $array
                ]
            ]
        ];
        return $data;
    }
}
