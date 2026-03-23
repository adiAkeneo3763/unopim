<?php

namespace Webkul\Admin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Webkul\Admin\Helpers\Dashboard;

class DashboardController extends Controller
{
    /**
     * Request param functions
     *
     * @var array
     */
    protected $typeFunctions = [
        'total-catalogs'        => 'getTotalCatalogs',
        'total-configurations'  => 'getTotalConfigurations',
        'product-stats'         => 'getProductStats',
        'recent-activity'       => 'getRecentActivity',
        'data-transfer-status'  => 'getDataTransferStatus',
        'needs-attention'       => 'getNeedsAttention',
        'channel-readiness'     => 'getChannelReadiness',
    ];

    /**
     * Create a controller instance.
     *
     * @return void
     */
    public function __construct(protected Dashboard $dashboardHelper) {}

    /**
     * Dashboard page.
     *
     * @return View|JsonResponse
     */
    public function index()
    {
        return view('admin::dashboard.index')->with([

        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function stats()
    {
        $type = request()->query('type');

        if (! isset($this->typeFunctions[$type])) {
            return response()->json(['error' => 'Invalid type'], 400);
        }

        $stats = $this->dashboardHelper->{$this->typeFunctions[$type]}();

        return response()->json([
            'statistics' => $stats,
        ]);
    }
}
