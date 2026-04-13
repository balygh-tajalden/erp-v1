<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckStatusRequest;
use App\Http\Requests\Api\RequestLicenseRequest;
use App\Services\ClientLicenseService;
use App\Traits\ApiResponseTrait;

class ClientsSMSController extends Controller
{
    use ApiResponseTrait;

    protected $licenseService;

    public function __construct(ClientLicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * التحقق من حالة الجهاز والحساب
     */
    public function checkStatus(CheckStatusRequest $request)
    {
        $authResult = $this->licenseService->authenticate(
            $request->username,
            $request->password
        );

        if (!$authResult['success']) {
            return $this->errorResponse($authResult['error_code'], $authResult['message'], $authResult['status_code']);
        }

        $statusResult = $this->licenseService->checkDeviceStatus(
            $authResult['account'],
            $request->subscription_key
        );

        if (!$statusResult['success']) {
            return $this->errorResponse($statusResult['error_code'], $statusResult['message'], $statusResult['status_code']);
        }

        return $this->successResponse(['balance' => $statusResult['balance']]);
    }

    /**
     * طلب ترخيص لجهاز جديد
     */
    public function requestLicense(RequestLicenseRequest $request)
    {
        $authResult = $this->licenseService->authenticate(
            $request->username,
            $request->password
        );

        if (!$authResult['success']) {
            return $this->errorResponse($authResult['error_code'], $authResult['message'], $authResult['status_code']);
        }

        $licenseResult = $this->licenseService->handleLicenseRequest(
            $authResult['account'],
            $request->subscription_key,
            $request->device_name
        );

        if (!$licenseResult['success']) {
            return $this->errorResponse($licenseResult['error_code'], $licenseResult['message'], $licenseResult['status_code']);
        }

        return $this->successResponse([], $licenseResult['message']);
    }
}
