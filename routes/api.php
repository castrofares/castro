<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\LocalAdminController;
use App\Http\Controllers\ContactInfoController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\RenterController;
use App\Http\Controllers\ConsultantController;
use App\Http\Controllers\MarketingManagerController;
use App\Http\Controllers\DeliveryCompanyController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']); // تسجيل مستخدم جديد (للبائع والأدوار الأخرى)
    Route::post('/login', [AuthController::class, 'login']);       // تسجيل الدخول للجميع
    Route::post('/registerBuyer', [AuthController::class, 'registerBuyer']); // تسجيل باير جديد فقط

    Route::middleware('auth:sanctum')->group(function () {
       Route::post('/logout', [AuthController::class, 'logout']); // تسجيل الخروج اختياري
       // Route::get('/me', [AuthController::class, 'me']);          // الحصول على بيانات المستخدم الحالي
    });
});
Route::middleware(['auth:sanctum'])->group(function () {
    // مسارات السوبر أدمن
    Route::middleware('super_admin')->prefix('super-admin')->group(function () {
        Route::get('/dashboard/stats', [SuperAdminController::class, 'getDashboardStats']);//احصائيات Dashboard

        Route::post('/local-admin', [SuperAdminController::class, 'createLocalAdmin']);//اضافة لوكال ادمن
        Route::delete('/local-admin/{id}', [SuperAdminController::class, 'deleteLocalAdmin']);//حذف لوكال ادمن
        Route::get('/local-admin/{id}', [SuperAdminController::class, 'showLocalAdmin']);//عرض بيانات لوكال ادمن واحد
        Route::get('/local-admins', [SuperAdminController::class, 'listLocalAdmins']);//عرض قائمة بيانات جميع اللوكال ادمن

        Route::put('/local-admin/{id}', [SuperAdminController::class, 'updateLocalAdmin']);//تعديل بيانات لوكال ادمن
        Route::patch('/cars/{id}/approve', [SuperAdminController::class, 'approveCar']);//قبول سيارة لكي تعرض للباير
        Route::patch('/cars/{id}/reject', [SuperAdminController::class, 'rejectCar']);//رفض سيارة بالتالي لاتعرض للباير
        //قائمة بجميع السيارت المعلقة التي حالتها pending
        //اي ان اللوكال ادمن اضاف سيارات والسوبر ادمن لم يتم قبولهم او رفضهم
        Route::get('/cars/pending', [SuperAdminController::class, 'listPendingCars']);

        // مسارات إدارة البائعين
        Route::get('/sellers/pending', [SuperAdminController::class, 'listPendingSellers']); // قائمة البائعين قيد المراجعة
        Route::patch('/sellers/{id}/approve', [SuperAdminController::class, 'approveSeller']); // الموافقة على بائع
        Route::patch('/sellers/{id}/reject', [SuperAdminController::class, 'rejectSeller']); // رفض بائع
        Route::patch('/sellers/{id}/suspend', [SuperAdminController::class, 'suspendSeller']); // تعليق حساب بائع
        Route::patch('/sellers/{id}/activate', [SuperAdminController::class, 'activateSeller']); // تفعيل حساب بائع
    });





//مسارات اللوكال ادمن
Route::middleware(['auth:sanctum', 'local_admin'])->prefix('local-admin')->group(function () {
    Route::post('/cars', [LocalAdminController::class, 'createCar']);//اضافة سيارة
    Route::delete('/cars/{id}', [LocalAdminController::class, 'deleteCar']);//حذف سيارة
    Route::put('/cars/{id}', [LocalAdminController::class, 'updateCar']);//تعديل بيانات سيارة واحدة
    Route::get('/cars/{id}', [LocalAdminController::class, 'showCar']);//عرض سيارة واحدة
    Route::get('/cars', [LocalAdminController::class, 'listCars']);//عرض قائمة بالسيارت التي اضافهم صاحب الحساب فقط

    // مسارات معلومات الاتصال (Contact Info)
    Route::get('/contact-info', [ContactInfoController::class, 'show']);//عرض معلومات الاتصال
    Route::post('/contact-info', [ContactInfoController::class, 'store']);//إضافة أو تحديث معلومات الاتصال
    Route::delete('/contact-info', [ContactInfoController::class, 'destroy']);//حذف معلومات الاتصال

    // مسارات التقارير (Reports)
    Route::get('/reports', [ReportsController::class, 'index']);//عرض قائمة بجميع التقارير
    Route::post('/reports', [ReportsController::class, 'store']);//إنشاء تقرير جديد
    Route::get('/reports/{id}', [ReportsController::class, 'show']);//عرض تقرير واحد
    Route::post('/reports/{id}/send', [ReportsController::class, 'send']);//إرسال تقرير للسوبر أدمن
    Route::delete('/reports/{id}', [ReportsController::class, 'destroy']);//حذف تقرير
});



Route::get('/cars/images/{filename}', function ($filename) {
    $path = storage_path('app/cars/' . $filename);

    if (!file_exists($path)) {
        return response()->json(['error' => 'Image not found'], 404);
    }

    $mimeType = mime_content_type($path);
    return response()->file($path, ['Content-Type' => $mimeType]);
});


Route::middleware(['auth:sanctum', 'buyer'])->prefix('buyer')->group(function () {
    Route::get('/cars', [BuyerController::class, 'listApprovedCars']);
    Route::get('/cars/{id}', [BuyerController::class, 'showCar']);
    Route::post('/cart/{id}', [BuyerController::class, 'addToCart']);
    Route::get('/cart', [BuyerController::class, 'viewCart']);
    Route::delete('/cart/{id}', [BuyerController::class, 'removeFromCart']);
    Route::post('/purchase', [BuyerController::class, 'purchaseAll']);
    Route::post('/compare', [BuyerController::class, 'compareAndSave']);
    Route::get('/search', [BuyerController::class, 'searchCars']);
});

// مسارات البائع (Seller)
Route::middleware(['auth:sanctum', 'seller'])->prefix('seller')->group(function () {
    // إدارة السيارات
    Route::post('/cars', [SellerController::class, 'createCar']); // إضافة سيارة
    Route::get('/cars', [SellerController::class, 'listMyCars']); // عرض سيارات البائع
    Route::put('/cars/{id}', [SellerController::class, 'updateCar']); // تعديل سيارة
    Route::delete('/cars/{id}', [SellerController::class, 'deleteCar']); // حذف سيارة

    // المبيعات والأرباح
    Route::get('/sales', [SellerController::class, 'viewSales']); // عرض المبيعات
    Route::get('/earnings', [SellerController::class, 'viewEarnings']); // عرض الأرباح والإحصائيات

    // معلومات الاتصال
    Route::get('/contact-info', [SellerController::class, 'getContactInfo']); // عرض معلومات الاتصال
    Route::post('/contact-info', [SellerController::class, 'updateContactInfo']); // إضافة/تحديث معلومات الاتصال
    Route::delete('/contact-info', [SellerController::class, 'deleteContactInfo']); // حذف معلومات الاتصال
});

// مسارات المستأجر (Renter)
Route::middleware(['auth:sanctum', 'renter'])->prefix('renter')->group(function () {
    Route::get('/rentals/available', [RenterController::class, 'listAvailableRentals']);
    Route::post('/rentals/book', [RenterController::class, 'bookRental']);
    Route::get('/rentals', [RenterController::class, 'myRentals']);
    Route::delete('/rentals/{id}/cancel', [RenterController::class, 'cancelRental']);
    Route::put('/rentals/{id}/extend', [RenterController::class, 'extendRental']);
});

// مسارات المستشار (Consultant)
Route::middleware(['auth:sanctum', 'consultant'])->prefix('consultant')->group(function () {
    Route::get('/consultations', [ConsultantController::class, 'listRequests']);
    Route::post('/consultations/{id}/accept', [ConsultantController::class, 'acceptRequest']);
    Route::post('/consultations/{id}/respond', [ConsultantController::class, 'respondToConsultation']);
    Route::get('/earnings', [ConsultantController::class, 'viewEarnings']);
});

// مسارات مدير التسويق (Marketing Manager)
Route::middleware(['auth:sanctum', 'marketing_manager'])->prefix('marketing')->group(function () {
    Route::post('/offers', [MarketingManagerController::class, 'createOffer']);
    Route::get('/offers', [MarketingManagerController::class, 'listOffers']);
    Route::put('/offers/{id}', [MarketingManagerController::class, 'updateOffer']);
    Route::delete('/offers/{id}', [MarketingManagerController::class, 'deleteOffer']);
    Route::post('/campaigns', [MarketingManagerController::class, 'createCampaign']);
    Route::get('/campaigns', [MarketingManagerController::class, 'listCampaigns']);
});

// مسارات شركة التوصيل (Delivery Company)
Route::middleware(['auth:sanctum', 'delivery_company'])->prefix('delivery')->group(function () {
    Route::get('/requests', [DeliveryCompanyController::class, 'listDeliveryRequests']);
    Route::post('/requests/{id}/accept', [DeliveryCompanyController::class, 'acceptDelivery']);
    Route::put('/requests/{id}/status', [DeliveryCompanyController::class, 'updateDeliveryStatus']);
    Route::get('/earnings', [DeliveryCompanyController::class, 'viewEarnings']);
});

});



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
