<?php

namespace Juzaweb\Modules\Api\Http\Controllers\API\Pages;

use Illuminate\Http\JsonResponse;
use Juzaweb\Modules\Api\Http\Resources\Pages\PageResource;
use Juzaweb\Modules\Core\Http\Controllers\APIController;
use Juzaweb\Modules\Core\Models\Pages\Page;
use OpenApi\Annotations as OA;

class PageController extends APIController
{
    /**
     * @OA\Get(
     *      path="/api/v1/pages/{slug}",
     *      tags={"Pages"},
     *      summary="Get page details by slug",
     *
     *      @OA\Parameter(
     *          name="slug",
     *          in="path",
     *          required=true,
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\Response(response=200, description="Successful operation"),
     *      @OA\Response(response=404, description="Page not found")
     * )
     */
    public function show(string $slug): JsonResponse
    {
        $page = Page::whereTranslation('slug', $slug)->firstOrFail();

        return $this->restSuccess(
            (new PageResource($page))->resolve()
        );
    }
}
