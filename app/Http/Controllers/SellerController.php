<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\Sale;
use App\Models\SellerContactInfo;
use Illuminate\Support\Facades\Storage;

class SellerController extends Controller
{
    /**
     * إضافة سيارة للبيع (Seller)
     */
    public function createCar(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'brand'       => 'required|string|max:255',
            'model'       => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'year'        => 'nullable|string',
            'transmission' => 'nullable|in:manual,automatic,both',
            'fuel_type'   => 'nullable|string',
            'color'       => 'nullable|string',
            'location'    => 'nullable|string',
            'mileage'     => 'nullable|integer',
            'doors'       => 'nullable|integer',
            'seats'       => 'nullable|integer',
            'engine_size' => 'nullable|string',
        ]);

        $car = Car::create([
            'name'        => $validated['name'],
            'brand'       => $validated['brand'],
            'model'       => $validated['model'],
            'price'       => $validated['price'],
            'description' => $validated['description'] ?? null,
            'user_id'     => auth()->id(),
            'status'      => 'pending',
            'availability' => 'sale',
            'year'        => $validated['year'] ?? null,
            'transmission' => $validated['transmission'] ?? 'manual',
            'fuel_type'   => $validated['fuel_type'] ?? null,
            'color'       => $validated['color'] ?? null,
            'location'    => $validated['location'] ?? null,
            'mileage'     => $validated['mileage'] ?? null,
            'doors'       => $validated['doors'] ?? null,
            'seats'       => $validated['seats'] ?? null,
            'engine_size' => $validated['engine_size'] ?? null,
        ]);

        return response()->json([
            'message' => 'تم إضافة السيارة بنجاح وفي انتظار موافقة الإدارة',
            'car'     => $car,
        ], 201);
    }

    /**
     * عرض سيارات البائع
     */
    public function listMyCars()
    {
        $cars = Car::where('user_id', auth()->id())
            ->with('images')
            ->get();

        return response()->json([
            'message' => 'قائمة سياراتك',
            'cars'    => $cars,
        ], 200);
    }

    /**
     * تعديل سيارة
     */
    public function updateCar(Request $request, $id)
    {
        $car = Car::where('user_id', auth()->id())->find($id);

        if (!$car) {
            return response()->json(['message' => 'السيارة غير موجودة'], 404);
        }

        $validated = $request->validate([
            'name'        => 'nullable|string|max:255',
            'brand'       => 'nullable|string|max:255',
            'model'       => 'nullable|string|max:255',
            'price'       => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'year'        => 'nullable|string',
            'transmission' => 'nullable|in:manual,automatic,both',
            'fuel_type'   => 'nullable|string',
            'color'       => 'nullable|string',
            'location'    => 'nullable|string',
            'mileage'     => 'nullable|integer',
        ]);

        $car->update(array_filter($validated));

        return response()->json([
            'message' => 'تم تحديث السيارة بنجاح',
            'car'     => $car,
        ], 200);
    }

    /**
     * حذف سيارة
     */
    public function deleteCar($id)
    {
        $car = Car::where('user_id', auth()->id())->find($id);

        if (!$car) {
            return response()->json(['message' => 'السيارة غير موجودة'], 404);
        }

        $car->delete();

        return response()->json(['message' => 'تم حذف السيارة بنجاح'], 200);
    }

    /**
     * عرض المبيعات
     */
    public function viewSales()
    {
        $sales = Sale::where('seller_id', auth()->id())
            ->with(['car', 'buyer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'قائمة مبيعاتك',
            'sales'   => $sales,
        ], 200);
    }

    /**
     * عرض الأرباح
     */
    public function viewEarnings()
    {
        $userId = auth()->id();

        // إجمالي الأرباح من المبيعات المكتملة
        $totalEarnings = Sale::where('seller_id', $userId)
            ->where('status', 'completed')
            ->sum('sale_price');

        // عدد المبيعات المكتملة
        $completedSales = Sale::where('seller_id', $userId)
            ->where('status', 'completed')
            ->count();

        // عدد المبيعات المعلقة
        $pendingSales = Sale::where('seller_id', $userId)
            ->where('status', 'pending')
            ->count();

        // عدد السيارات المعروضة
        $totalCars = Car::where('user_id', $userId)->count();

        // السيارات المعتمدة
        $approvedCars = Car::where('user_id', $userId)
            ->where('status', 'approved')
            ->count();

        return response()->json([
            'message' => 'احصائيات الأرباح',
            'data' => [
                'total_earnings' => $totalEarnings,
                'completed_sales' => $completedSales,
                'pending_sales' => $pendingSales,
                'total_cars' => $totalCars,
                'approved_cars' => $approvedCars,
            ],
        ], 200);
    }

    /**
     * عرض معلومات الاتصال للبائع
     */
    public function getContactInfo()
    {
        $contactInfo = SellerContactInfo::where('user_id', auth()->id())->first();

        return response()->json([
            'message' => $contactInfo ? 'معلومات الاتصال' : 'لا توجد معلومات اتصال',
            'data' => $contactInfo,
        ], 200);
    }

    /**
     * إضافة أو تحديث معلومات الاتصال
     */
    public function updateContactInfo(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string|max:20',
            'secondary_phone' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'whatsapp' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'social_media' => 'nullable|array',
            'social_media.facebook' => 'nullable|url',
            'social_media.instagram' => 'nullable|url',
            'social_media.twitter' => 'nullable|url',
            'notes' => 'nullable|string',
        ]);

        $contactInfo = SellerContactInfo::updateOrCreate(
            ['user_id' => auth()->id()],
            $validated
        );

        return response()->json([
            'message' => 'تم حفظ معلومات الاتصال بنجاح',
            'data' => $contactInfo,
        ], 200);
    }

    /**
     * حذف معلومات الاتصال
     */
    public function deleteContactInfo()
    {
        $contactInfo = SellerContactInfo::where('user_id', auth()->id())->first();

        if (!$contactInfo) {
            return response()->json(['message' => 'لا توجد معلومات اتصال للحذف'], 404);
        }

        $contactInfo->delete();

        return response()->json(['message' => 'تم حذف معلومات الاتصال بنجاح'], 200);
    }
}
