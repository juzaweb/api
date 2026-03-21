<?php

namespace Juzaweb\Modules\Api\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Juzaweb\Modules\Api\Http\Requests\Profile\UpdatePasswordRequest;
use Juzaweb\Modules\Api\Http\Requests\Profile\UpdateProfileRequest;
use Juzaweb\Modules\Core\Http\Controllers\APIController;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="User model",
 *
 *     @OA\Property(property="id", type="string", format="uuid", example="123e4567-e89b-12d3-a456-426614174000"),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
 *     @OA\Property(property="birthday", type="string", format="date", example="1990-01-01"),
 *     @OA\Property(property="status", type="string", example="active"),
 *     @OA\Property(property="avatar", type="string", example="https://example.com/avatar.jpg"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00.000000Z")
 * )
 */
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
     *              @OA\Property(property="data", ref="#/components/schemas/User")
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

    /**
     * @OA\Put(
     *      path="/api/v1/profile",
     *      tags={"Profile"},
     *      summary="Update user profile",
     *      description="Updates the authenticated user's profile information.",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(property="name", type="string", example="John Doe"),
     *              @OA\Property(property="birthday", type="string", format="date", example="1990-01-01")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(property="data", ref="#/components/schemas/User")
     *          )
     *      ),
     *
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->fill($request->only(['name', 'birthday']));
        $user->save();

        $user = clone $user;
        $user->mergeCasts(['status' => 'string']);

        return $this->restSuccess($user);
    }

    /**
     * @OA\Put(
     *      path="/api/v1/profile/password",
     *      tags={"Profile"},
     *      summary="Update user password",
     *      description="Updates the authenticated user's password.",
     *      security={{"bearerAuth":{}}},
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(property="current_password", type="string", example="secret123"),
     *              @OA\Property(property="password", type="string", example="newsecret123"),
     *              @OA\Property(property="password_confirmation", type="string", example="newsecret123")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string")
     *          )
     *      ),
     *
     *      @OA\Response(response=401, description="Unauthorized"),
     *      @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->post('current_password'), $user->password)) {
            return response()->json([
                'message' => trans('juzaweb::app.current_password_incorrect'),
                'errors' => [
                    'current_password' => [trans('juzaweb::app.current_password_incorrect')],
                ],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->post('password')),
        ]);

        return $this->restSuccess([], trans('juzaweb::app.updated_successfully'));
    }
}
