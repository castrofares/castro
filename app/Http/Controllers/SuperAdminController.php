<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Car;

class SuperAdminController extends Controller
{
    /**
     * إضافة لوكل أدمن.
     */
    public function createLocalAdmin(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $localAdmin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => 'local_admin',
        ]);

        return response()->json([
            'message' => 'Local Admin created successfully.',
            'local_admin' => $localAdmin,
        ], 201);
    }

    /**
     * حذف لوكل أدمن.
     */
    public function deleteLocalAdmin($id)
    {
        $localAdmin = User::where('role', 'local_admin')->find($id);

        if (!$localAdmin) {
            return response()->json(['message' => 'Local Admin not found'], 404);
        }

        $localAdmin->delete();

        return response()->json([
            'message' => 'Local Admin deleted successfully.',
        ], 200);
    }

    /**
     * عرض بيانات لوكل أدمن.
     */
    public function showLocalAdmin($id)
    {
        $localAdmin = User::where('role', 'local_admin')->find($id);

        if (!$localAdmin) {
            return response()->json(['message' => 'Local Admin not found'], 404);
        }

        return response()->json([
            'local_admin' => $localAdmin,
        ], 200);
    }

    /**
     * عرض قائمة لوكل أدمن.
     */
    public function listLocalAdmins()
    {
        $localAdmins = User::where('role', 'local_admin')->get();

        return response()->json([
            'local_admins' => $localAdmins,
        ], 200);
    }

    /**
     * تعديل بيانات لوكل أدمن.
     */
    public function updateLocalAdmin(Request $request, $id)
    {
        $localAdmin = User::where('role', 'local_admin')->find($id);

        if (!$localAdmin) {
            return response()->json(['message' => 'Local Admin not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'string|max:255|nullable',
            'email' => 'email|unique:users,email,' . $id . '|nullable',
            'password' => 'string|min:8|nullable',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        }

        $localAdmin->update(array_filter($validated));

        return response()->json([
            'message' => 'Local Admin updated successfully.',
            'local_admin' => $localAdmin,
        ], 200);
    }

    /**
     * موافقة السوبر أدمن على السيارة.
     */
    public function approveCar($id)
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json(['message' => 'Car not found'], 404);
        }

        $car->update(['status' => 'approved']);

        return response()->json([
            'message' => 'Car approved successfully.',
            'car' => $car,
        ], 200);
    }

    /**
     * رفض السوبر أدمن للسيارة.
     */
    public function rejectCar($id)
    {
        $car = Car::find($id);

        if (!$car) {
            return response()->json(['message' => 'Car not found'], 404);
        }

        $car->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Car rejected successfully.',
            'car' => $car,
        ], 200);
    }

    public function listPendingCars()
    {
        $cars = Car::where('status', 'pending')->with('user', 'images')->get();

        return response()->json([
            'message' => 'Pending cars fetched successfully.',
            'cars' => $cars,
        ], 200);
    }

    /**
     * الحصول على إحصائيات Dashboard
     */
    public function getDashboardStats()
    {
        $totalUsers = User::count();
        $approvedCars = Car::where('status', 'approved')->count();
        $pendingCars = Car::where('status', 'pending')->count();

        $recentActivities = Car::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($car) {
                return [
                    'type' => $car->status === 'approved' ? 'approval' : 'new_listing',
                    'message' => $car->status === 'approved'
                        ? "تمت الموافقة على {$car->brand} {$car->model}"
                        : "سيارة جديدة بانتظار الموافقة: {$car->brand} {$car->model}",
                    'time' => $car->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'message' => 'Dashboard statistics fetched successfully.',
            'stats' => [
                'total_users' => $totalUsers,
                'approved_cars' => $approvedCars,
                'pending_cars' => $pendingCars,
                'monthly_revenue' => 0,
            ],
            'recent_activities' => $recentActivities,
        ], 200);
    }

    /**
     * عرض قائمة البائعين قيد المراجعة
     */
    public function listPendingSellers()
    {
        $pendingSellers = User::where('role', 'seller')
            ->where('status', 'pending')
            ->get();

        return response()->json([
            'message' => 'قائمة البائعين قيد المراجعة',
            'sellers' => $pendingSellers,
        ], 200);
    }

    /**
     * الموافقة على بائع
     */
    public function approveSeller($id)
    {
        $seller = User::where('role', 'seller')->find($id);

        if (!$seller) {
            return response()->json(['message' => 'البائع غير موجود'], 404);
        }

        if ($seller->status !== 'pending') {
            return response()->json(['message' => 'البائع ليس في حالة انتظار'], 400);
        }

        $seller->update(['status' => 'active']);

        return response()->json([
            'message' => 'تمت الموافقة على البائع بنجاح',
            'seller' => $seller,
        ], 200);
    }

    /**
     * رفض بائع
     */
    public function rejectSeller($id)
    {
        $seller = User::where('role', 'seller')->find($id);

        if (!$seller) {
            return response()->json(['message' => 'البائع غير موجود'], 404);
        }

        if ($seller->status !== 'pending') {
            return response()->json(['message' => 'البائع ليس في حالة انتظار'], 400);
        }

        // يمكن حذف الحساب أو تعليقه
        $seller->delete();

        return response()->json([
            'message' => 'تم رفض البائع وحذف الحساب',
        ], 200);
    }

    /**
     * تعليق حساب بائع
     */
    public function suspendSeller($id)
    {
        $seller = User::where('role', 'seller')->find($id);

        if (!$seller) {
            return response()->json(['message' => 'البائع غير موجود'], 404);
        }

        $seller->update(['status' => 'suspended']);

        return response()->json([
            'message' => 'تم تعليق حساب البائع',
            'seller' => $seller,
        ], 200);
    }

    /**
     * إعادة تفعيل حساب بائع
     */
    public function activateSeller($id)
    {
        $seller = User::where('role', 'seller')->find($id);

        if (!$seller) {
            return response()->json(['message' => 'البائع غير موجود'], 404);
        }

        $seller->update(['status' => 'active']);

        return response()->json([
            'message' => 'تم تفعيل حساب البائع',
            'seller' => $seller,
        ], 200);
    }
}
