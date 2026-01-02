<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Car;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    /**
     * Get all reports for the authenticated local admin
     */
    public function index()
    {
        try {
            $reports = Report::where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'message' => 'تم جلب التقارير بنجاح',
                'data' => $reports
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء جلب التقارير',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific report
     */
    public function show($id)
    {
        try {
            $report = Report::where('user_id', auth()->id())
                ->where('id', $id)
                ->first();

            if (!$report) {
                return response()->json([
                    'message' => 'التقرير غير موجود'
                ], 404);
            }

            return response()->json([
                'message' => 'تم جلب التقرير بنجاح',
                'data' => $report
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء جلب التقرير',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new report
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'type' => 'required|in:sales,inventory,performance,custom',
                'period_from' => 'required|date',
                'period_to' => 'required|date|after_or_equal:period_from',
                'notes' => 'nullable|string',
            ]);

            // Generate report data based on type
            $reportData = $this->generateReportData($validated['type'], $validated['period_from'], $validated['period_to']);

            $report = Report::create([
                'user_id' => auth()->id(),
                'title' => $validated['title'],
                'type' => $validated['type'],
                'period_from' => $validated['period_from'],
                'period_to' => $validated['period_to'],
                'notes' => $validated['notes'] ?? null,
                'data' => $reportData,
                'status' => 'completed',
            ]);

            return response()->json([
                'message' => 'تم إنشاء التقرير بنجاح',
                'data' => $report
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء إنشاء التقرير',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send report to super admin
     */
    public function send($id)
    {
        try {
            $report = Report::where('user_id', auth()->id())
                ->where('id', $id)
                ->first();

            if (!$report) {
                return response()->json([
                    'message' => 'التقرير غير موجود'
                ], 404);
            }

            if ($report->status === 'sent') {
                return response()->json([
                    'message' => 'التقرير تم إرساله مسبقاً'
                ], 400);
            }

            // Update report status
            $report->update([
                'status' => 'sent',
                'sent_at' => now(),
                'sent_to' => ['super_admin'] // يمكن توسيعها لإرسال لمستخدمين محددين
            ]);

            // TODO: Send email notification to super admin

            return response()->json([
                'message' => 'تم إرسال التقرير بنجاح',
                'data' => $report
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء إرسال التقرير',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a report
     */
    public function destroy($id)
    {
        try {
            $report = Report::where('user_id', auth()->id())
                ->where('id', $id)
                ->first();

            if (!$report) {
                return response()->json([
                    'message' => 'التقرير غير موجود'
                ], 404);
            }

            $report->delete();

            return response()->json([
                'message' => 'تم حذف التقرير بنجاح'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء حذف التقرير',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate report data based on type and period
     */
    private function generateReportData($type, $periodFrom, $periodTo)
    {
        $userId = auth()->id();

        // Get cars for this user within the period
        $cars = Car::where('user_id', $userId)
            ->whereBetween('created_at', [$periodFrom, $periodTo])
            ->get();

        $data = [
            'total_cars' => $cars->count(),
            'period_from' => $periodFrom,
            'period_to' => $periodTo,
        ];

        switch ($type) {
            case 'sales':
                $data['approved_cars'] = $cars->where('status', 'approved')->count();
                $data['pending_cars'] = $cars->where('status', 'pending')->count();
                $data['total_revenue'] = $cars->where('status', 'approved')->sum('price');
                break;

            case 'inventory':
                $data['available_cars'] = $cars->where('status', 'approved')->count();
                $data['pending_cars'] = $cars->where('status', 'pending')->count();
                $data['rejected_cars'] = $cars->where('status', 'rejected')->count();
                break;

            case 'performance':
                $totalCars = $cars->count();
                $approvedCars = $cars->where('status', 'approved')->count();
                $data['conversion_rate'] = $totalCars > 0 ? round(($approvedCars / $totalCars) * 100, 2) . '%' : '0%';
                $data['approved_cars'] = $approvedCars;
                $data['pending_cars'] = $cars->where('status', 'pending')->count();
                break;

            case 'custom':
                $data['total_cars'] = $cars->count();
                $data['approved_cars'] = $cars->where('status', 'approved')->count();
                break;
        }

        return $data;
    }
}
