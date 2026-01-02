<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\Cart;
use App\Models\Purchase;
use App\Models\Comparison;

class BuyerController extends Controller
{
    public function listApprovedCars()
{
    // جلب جميع السيارات التي حالتها "approved" مع الصور
    $cars = Car::where('status', 'approved')->with('images')->get();

    return response()->json([
        'message' => '🚗 Approved cars fetched successfully.',
        'cars'    => $cars,
    ], 200);
}
    //
    /**
     * عرض تفاصيل سيارة.
     */
    public function showCar($id)
    {
        $car = Car::with('images')->where('status', 'approved')->find($id);

        if (!$car) {
            return response()->json(['message' => 'Car not found or not approved'], 404);
        }

        return response()->json($car, 200);
    }

    public function addToCart(Request $request, $id)
    {
        $car = Car::where('status', 'approved')->find($id);

        if (!$car) {
            return response()->json(['message' => 'Car not found or not approved'], 404);
        }

        $exists = Cart::where('user_id', auth()->id())->where('car_id', $id)->exists();

        if ($exists) {
            return response()->json(['message' => 'Car already in cart'], 400);
        }

        Cart::create([
            'user_id' => auth()->id(),
            'car_id'  => $id,
        ]);

        return response()->json(['message' => 'Car added to cart successfully'], 201);
    }

    public function viewCart()
{
    // جلب جميع السيارات الموجودة في سلة المشتريات الخاصة بالمستخدم الحالي
    $cartItems = Cart::where('user_id', auth()->id())
        ->with('car.images') // جلب معلومات السيارة وصورها
        ->get();

    // التحقق من وجود سيارات في السلة
    if ($cartItems->isEmpty()) {
        return response()->json(['message' => 'Your cart is empty'], 404);
    }

    // إرسال السيارات الموجودة في السلة في الاستجابة
    return response()->json([
        'message' => 'Your cart items',
        'cart'    => $cartItems,
    ], 200);
}
     public function removeFromCart($id)
{
    $cartItem = Cart::where('user_id', auth()->id())->where('car_id', $id)->first();

    if (!$cartItem) {
        return response()->json(['message' => 'Car not found in cart'], 404);
    }

    $cartItem->delete();

    return response()->json(['message' => 'Car removed from cart successfully'], 200);
}


public function purchaseAll()
{
    // جلب جميع السيارات في سلة المشتريات الخاصة بالمستخدم الحالي
    $cartItems = Cart::where('user_id', auth()->id())->with('car')->get();

    // التحقق من أن السلة ليست فارغة
    if ($cartItems->isEmpty()) {
        return response()->json(['message' => 'Your cart is empty.'], 404);
    }

    $purchases = [];

    // إجراء عملية الشراء لكل سيارة في السلة
    foreach ($cartItems as $cartItem) {
        // التأكد من أن السيارة لها سعر
        if (!isset($cartItem->car->price)) {
            return response()->json(['message' => 'Car price is missing for car ID ' . $cartItem->car_id], 400);
        }

        // إنشاء عملية الشراء مع تضمين السعر
        $purchase = Purchase::create([
            'user_id' => auth()->id(),
            'car_id'  => $cartItem->car_id,
            'price'   => $cartItem->car->price, // استخدام سعر السيارة
        ]);

        // إضافة العملية إلى القائمة للإرجاع في الاستجابة
        $purchases[] = $purchase;

        // حذف السيارة من السلة
        $cartItem->delete();
    }

    return response()->json([
        'message'   => 'All cars purchased successfully.',
        'purchases' => $purchases,
    ], 201);
}
public function compareAndSave(Request $request)
{
    // التحقق من صحة البيانات المدخلة
    $request->validate([
        'car_id_1' => 'required|exists:cars,id',
        'car_id_2' => 'required|exists:cars,id|different:car_id_1',
    ]);

    // جلب السيارتين من قاعدة البيانات
    $cars = Car::whereIn('id', [$request->car_id_1, $request->car_id_2])
        ->where('status', 'approved')
        ->with('images')
        ->get();

    // التحقق من أن السيارتين موجودتين
    if ($cars->count() < 2) {
        return response()->json(['message' => 'One or both cars not found or not approved.'], 404);
    }

    // حفظ المقارنة في جدول `comparisons`
    $comparison = Comparison::create([
        'user_id'  => auth()->id(),
        'car_id_1' => $request->car_id_1,
        'car_id_2' => $request->car_id_2,
    ]);

    return response()->json([
        'message'    => 'Cars comparison saved successfully.',
        'comparison' => $comparison,
        'cars'       => $cars,
    ], 201);
}

public function searchCars(Request $request)
{
    // التحقق من صحة البيانات المدخلة
    $request->validate([
        'query' => 'required|string', // النص المطلوب البحث عنه
    ]);

    // الحصول على النص المطلوب البحث عنه
    $searchQuery = $request->query('query'); // الطريقة الصحيحة للوصول إلى المدخلات

    // البحث عن السيارات
    $cars = Car::where('status', 'approved') // فقط السيارات المقبولة
        ->where(function ($query) use ($searchQuery) {
            $query->where('name', 'like', '%' . $searchQuery . '%') // البحث في الاسم
                  ->orWhere('brand', 'like', '%' . $searchQuery . '%') // البحث في العلامة التجارية
                  ->orWhere('description', 'like', '%' . $searchQuery . '%'); // البحث في الوصف
        })
        ->with('images') // جلب الصور المرتبطة
        ->get();

    // التحقق إذا لم يتم العثور على أي سيارة
    if ($cars->isEmpty()) {
        return response()->json(['message' => 'No cars found matching your query.'], 404);
    }

    // الرد بالسيارات المطابقة
    return response()->json([
        'message' => 'Cars matching your query found.',
        'cars'    => $cars,
    ], 200);
}

}
