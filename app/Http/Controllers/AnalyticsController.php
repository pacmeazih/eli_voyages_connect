<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dossier;
use App\Models\Client;
use App\Models\Document;
use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Get analytics data
     */
    public function index(Request $request)
    {
        $period = $request->input('period', '12months'); // 7days, 30days, 12months
        
        return response()->json([
            'dossiers_over_time' => $this->getDossiersOverTime($period),
            'dossiers_by_status' => $this->getDossiersByStatus(),
            'packages_distribution' => $this->getPackagesDistribution(),
            'conversion_metrics' => $this->getConversionMetrics(),
            'performance_metrics' => $this->getPerformanceMetrics(),
        ]);
    }

    /**
     * Get dossiers created over time
     */
    private function getDossiersOverTime($period)
    {
        $query = Dossier::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        );

        switch ($period) {
            case '7days':
                $query->where('created_at', '>=', Carbon::now()->subDays(7));
                $groupFormat = '%Y-%m-%d';
                break;
            case '30days':
                $query->where('created_at', '>=', Carbon::now()->subDays(30));
                $groupFormat = '%Y-%m-%d';
                break;
            case '12months':
            default:
                $query->where('created_at', '>=', Carbon::now()->subMonths(12));
                $groupFormat = '%Y-%m';
                break;
        }

        $data = $query
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill missing dates/months with 0
        $result = [];
        if ($period === '12months') {
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i)->format('Y-m');
                $count = $data->firstWhere('date', 'like', $date . '%')->count ?? 0;
                $result[] = [
                    'label' => Carbon::now()->subMonths($i)->format('M Y'),
                    'value' => $count
                ];
            }
        } else {
            $days = $period === '7days' ? 7 : 30;
            for ($i = $days - 1; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->format('Y-m-d');
                $count = $data->firstWhere('date', $date)->count ?? 0;
                $result[] = [
                    'label' => Carbon::now()->subDays($i)->format('d M'),
                    'value' => $count
                ];
            }
        }

        return $result;
    }

    /**
     * Get dossiers grouped by status
     */
    private function getDossiersByStatus()
    {
        $statusLabels = [
            'draft' => 'Brouillon',
            'pending' => 'En attente',
            'in_progress' => 'En cours',
            'approved' => 'Approuvé',
            'rejected' => 'Rejeté',
            'completed' => 'Terminé',
        ];

        $data = Dossier::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        return $data->map(function ($item) use ($statusLabels) {
            return [
                'label' => $statusLabels[$item->status] ?? $item->status,
                'value' => $item->count,
                'status' => $item->status,
            ];
        })->values();
    }

    /**
     * Get packages distribution
     */
    private function getPackagesDistribution()
    {
        $data = Dossier::select('package_id', DB::raw('COUNT(*) as count'))
            ->whereNotNull('package_id')
            ->with('package')
            ->groupBy('package_id')
            ->get();

        return $data->map(function ($item) {
            return [
                'label' => $item->package->name ?? 'Sans package',
                'value' => $item->count,
            ];
        })->values();
    }

    /**
     * Get conversion metrics
     */
    private function getConversionMetrics()
    {
        $total = Dossier::count();
        $completed = Dossier::where('status', 'completed')->count();
        $approved = Dossier::where('status', 'approved')->count();
        $rejected = Dossier::where('status', 'rejected')->count();
        $inProgress = Dossier::whereIn('status', ['pending', 'in_progress'])->count();

        $conversionRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;
        $approvalRate = $total > 0 ? round((($approved + $completed) / $total) * 100, 1) : 0;
        $rejectionRate = $total > 0 ? round(($rejected / $total) * 100, 1) : 0;

        return [
            'conversion_rate' => $conversionRate,
            'approval_rate' => $approvalRate,
            'rejection_rate' => $rejectionRate,
            'in_progress_count' => $inProgress,
        ];
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics()
    {
        // Average time from creation to completion (in days)
        $avgCompletionTime = Dossier::where('status', 'completed')
            ->whereNotNull('updated_at')
            ->get()
            ->avg(function ($dossier) {
                return $dossier->created_at->diffInDays($dossier->updated_at);
            });

        // Documents per dossier
        $avgDocumentsPerDossier = Document::count() / max(Dossier::count(), 1);

        // Active clients (with at least one dossier in last 3 months)
        $activeClients = Dossier::where('created_at', '>=', Carbon::now()->subMonths(3))
            ->distinct('client_id')
            ->count('client_id');

        // Monthly growth rate
        $currentMonth = Dossier::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();
        
        $lastMonth = Dossier::whereYear('created_at', Carbon::now()->subMonth()->year)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->count();

        $growthRate = $lastMonth > 0 ? round((($currentMonth - $lastMonth) / $lastMonth) * 100, 1) : 0;

        return [
            'avg_completion_time_days' => round($avgCompletionTime ?? 0, 1),
            'avg_documents_per_dossier' => round($avgDocumentsPerDossier, 1),
            'active_clients' => $activeClients,
            'monthly_growth_rate' => $growthRate,
        ];
    }
}
