<?php

namespace Juzaweb\Modules\Api\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Juzaweb\Modules\Core\Facades\Setting;
use Juzaweb\Modules\Core\Http\Controllers\APIController;
use Juzaweb\Modules\Core\Translations\Models\Language;
use OpenApi\Annotations as OA;

class SettingController extends APIController
{
    /**
     * @OA\Get(
     *      path="/api/v1/settings",
     *      tags={"Settings"},
     *      summary="Get site settings",
     *      description="Returns key-value pairs of public site settings such as title, logo, language, etc.",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Property(property="title", type="string", example="My Site"),
     *                  @OA\Property(property="description", type="string", example="Site description"),
     *                  @OA\Property(property="sitename", type="string", example="mysite"),
     *                  @OA\Property(property="logo", type="string", example="https://example.com/logo.png"),
     *                  @OA\Property(property="favicon", type="string", example="https://example.com/favicon.ico"),
     *                  @OA\Property(property="banner", type="string", example="https://example.com/banner.jpg"),
     *                  @OA\Property(property="language", type="string", example="en"),
     *                  @OA\Property(property="captcha", type="string", example="recaptcha"),
     *                  @OA\Property(property="captcha_site_key", type="string", example="site_key"),
     *                  @OA\Property(
     *                      property="languages",
     *                      type="object",
     *                      description="Available languages with code as key and name as value",
     *                      example={"en": "English", "vi": "Vietnamese", "ja": "Japanese"},
     *
     *                      @OA\AdditionalProperties(
     *                          type="string",
     *                          description="Language name, e.g. English, Vietnamese, Japanese"
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(response=500, description="Server error", ref="#/components/responses/error_500")
     * )
     */
    public function index(): JsonResponse
    {
        $keys = apply_filters(
            'jw_api_setting_keys',
            [
                'title',
                'description',
                'sitename',
                'logo',
                'favicon',
                'banner',
                'language',
                'captcha',
                'captcha_site_key',
            ]
        );

        return $this->restSuccess(
            [
                ...Setting::gets($keys),
                'languages' => Language::languages()->mapWithKeys(function ($item) {
                    return [$item->code => $item->name];
                })->toArray(),
            ]
        );
    }
}
