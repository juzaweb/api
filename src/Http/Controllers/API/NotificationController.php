<?php

namespace Juzaweb\Modules\Api\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Juzaweb\Modules\Core\Http\Controllers\APIController;
use Juzaweb\Modules\Core\Http\Resources\NotificationResource;
use OpenApi\Annotations as OA;

class NotificationController extends APIController
{
    /**
     * @OA\Get(
     *      path="/api/v1/notifications",
     *      tags={"Notification"},
     *      summary="Get user notifications",
     *      description="Returns the authenticated user's notifications.",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/NotificationResource"))
     *          )
     *      ),
     *
     *      @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user->notifications()->paginate(10);

        return response()->json(
            NotificationResource::collection($notifications)->response()->getData(true)
        );
    }
}
