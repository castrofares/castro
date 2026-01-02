<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\Rental;
use Carbon\Carbon;

class RenterController extends Controller
{
    /**
     * عرض السيارات المتاحة للإيجار
     */
    public function listAvailableRentals()
    {
        $cars = Car::where('status', 'approved')
            ->whereIn('availability', ['rent', 'both'])
            ->with('images')
            ->get();

        return response()->json([
            'message' => 'السيارات المتاحة للإيجار',
            'cars'    => $cars,
        ], 200);
    }

    /**
     * حجز سيارة للإيجار
     */
    public function bookRental(Request $request)
    {
        $validated = $request->validate([
            'car_id'           => 'required|exists:cars,id',
            'start_date'       => 'required|date|after:now',
            'end_date'         => 'required|date|after:start_date',
            'rental_type'      => 'required|in:hourly,daily,weekly,monthly',
            'pickup_location'  => 'nullable|string',
            'return_location'  => 'nullable|string',
            'notes'            => 'nullable|string',
        ]);

        $car = Car::find($validated['car_id']);

        if (!$car || !in_array($car->availability, ['rent', 'both'])) {
            return response()->json(['message' => 'السيارة غير متاحة للإيجار'], 400);
        }

        // حساب السعر الإجمالي
        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);

        $totalPrice = 0;
        switch ($validated['rental_type']) {
            case 'hourly':
                $hours = $start->diffInHours($end);
                $totalPrice = $hours * ($car->rental_price_hourly ?? 0);
                break;
            case 'daily':
                $days = $start->diffInDays($end);
                $totalPrice = $days * ($car->rental_price_daily ?? 0);
                break;
            case 'weekly':
                $weeks = $start->diffInWeeks($end);
                $totalPrice = $weeks * ($car->rental_price_weekly ?? 0);
                break;
            case 'monthly':
                $months = $start->diffInMonths($end);
                $totalPrice = $months * ($car->rental_price_monthly ?? 0);
                break;
        }

        $rental = Rental::create([
            'user_id'          => auth()->id(),
            'car_id'           => $validated['car_id'],
            'start_date'       => $validated['start_date'],
            'end_date'         => $validated['end_date'],
            'rental_type'      => $validated['rental_type'],
            'total_price'      => $totalPrice,
            'status'           => 'pending',
            'pickup_location'  => $validated['pickup_location'] ?? null,
            'return_location'  => $validated['return_location'] ?? null,
            'notes'            => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'تم حجز السيارة بنجاح',
            'rental'  => $rental,
        ], 201);
    }

    /**
     * عرض حجوزاتي
     */
    public function myRentals()
    {
        $rentals = Rental::where('user_id', auth()->id())
            ->with('car.images')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'قائمة حجوزاتك',
            'rentals' => $rentals,
        ], 200);
    }

    /**
     * إلغاء حجز
     */
    public function cancelRental($id)
    {
        $rental = Rental::where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        if (!$rental) {
            return response()->json(['message' => 'الحجز غير موجود'], 404);
        }

        if ($rental->status === 'completed') {
            return response()->json(['message' => 'لا يمكن إلغاء حجز مكتمل'], 400);
        }

        $rental->update(['status' => 'cancelled']);

        return response()->json(['message' => 'تم إلغاء الحجز بنجاح'], 200);
    }

    /**
     * تمديد فترة الإيجار
     */
    public function extendRental(Request $request, $id)
    {
        $validated = $request->validate([
            'new_end_date' => 'required|date|after:end_date',
        ]);

        $rental = Rental::where('user_id', auth()->id())
            ->where('id', $id)
            ->first();

        if (!$rental) {
            return response()->json(['message' => 'الحجز غير موجود'], 404);
        }

        if ($rental->status !== 'active') {
            return response()->json(['message' => 'يمكن تمديد الحجوزات النشطة فقط'], 400);
        }

        $rental->update(['end_date' => $validated['new_end_date']]);

        return response()->json([
            'message' => 'تم تمديد فترة الإيجار بنجاح',
            'rental'  => $rental,
        ], 200);
    }
}
