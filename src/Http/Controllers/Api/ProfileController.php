<?php

namespace Juzaweb\Modules\Api\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Juzaweb\Modules\Core\Http\Controllers\APIController;
use OpenApi\Annotations as OA;

class ProfileController extends APIController
{
    /**
     * @OA\Get(
     *      path="/api/v1/profile",
     *      tags={"Profile"},
     *      summary="Get user profile",
     *      description="Returns the authenticated user's profile information.",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(property="data", type="object")
     *          )
     *      ),
     *
     *      @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(Request $request): JsonResponse
    {
        $user = clone $request->user();
        $user->mergeCasts(['status' => 'string']);

        return $this->restSuccess($user);
    }
}
