<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * ویژگی پاسخ‌دهی یکپارچه JSON
 *
 * این ویژگی (Trait) روش‌های کمکی برای ارسال پاسخ‌های JSON یکپارچه و استاندارد
 * از کنترلرها فراهم می‌کند. همه پاسخ‌ها دارای ساختار مشترک شامل status،
 * message، data و در صورت نیاز errors و meta هستند.
 */
trait ApiResponse
{
    /**
     * ارسال پاسخ موفقیت‌آمیز
     *
     * پاسخ JSON با وضعیت موفق (true) و کد وضعیت HTTP مشخص برمی‌گرداند.
     *
     * @param mixed $data اطلاعاتی که باید در پاسخ ارسال شود
     * @param string $message پیام توضیحی موفقیت
     * @param int $code کد وضعیت HTTP (پیش‌فرض: ۲۰۰)
     *
     * @return \Illuminate\Http\JsonResponse پاسخ JSON
     */
    protected function successResponse($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * ارسال پاسخ خطا
     *
     * پاسخ JSON با وضعیت خطا (false) و کد وضعیت HTTP مشخص برمی‌گرداند.
     * می‌تواند شامل لیست خطاهای تفصیلی باشد.
     *
     * @param string $message پیام توضیحی خطا
     * @param int $code کد وضعیت HTTP (پیش‌فرض: ۴۰۰)
     * @param array<string, mixed> $errors آرایه جزئیات خطاها
     *
     * @return \Illuminate\Http\JsonResponse پاسخ JSON خطا
     */
    protected function errorResponse(string $message = 'Error', int $code = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $code);
    }

    /**
     * ارسال پاسخ صفحه‌بندی شده
     *
     * پاسخ JSON با اطلاعات صفحه‌بندی شامل داده‌ها و متادیتای صفحه‌بندی
     * (شماره صفحه فعلی، آخرین صفحه، تعداد در هر صفحه و مجموع کل) برمی‌گرداند.
     *
     * @param \Illuminate\Http\Resources\Json\ResourceCollection $collection مجموعه صفحه‌بندی شده
     * @param string $message پیام توضیحی
     *
     * @return \Illuminate\Http\JsonResponse پاسخ JSON با متادیتای صفحه‌بندی
     */
    protected function paginatedResponse(ResourceCollection $collection, string $message = 'Success'): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $collection->response()->getData(true)['data'],
            'meta' => [
                'current_page' => $collection->resource->currentPage(),
                'last_page' => $collection->resource->lastPage(),
                'per_page' => $collection->resource->perPage(),
                'total' => $collection->resource->total(),
            ],
        ]);
    }
}
