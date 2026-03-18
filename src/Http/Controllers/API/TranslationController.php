<?php

namespace Juzaweb\Modules\Api\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Juzaweb\Modules\Core\Facades\Module;
use Juzaweb\Modules\Core\Facades\Theme;
use Juzaweb\Modules\Core\Http\Controllers\APIController;
use Juzaweb\Modules\Core\Translations\Contracts\Translation as TranslationContract;
use Juzaweb\Modules\Core\Translations\Models\LanguageLine;
use OpenApi\Annotations as OA;

class TranslationController extends APIController
{
    public function __construct(protected TranslationContract $translationManager) {}

    /**
     * @OA\Get(
     *      path="/api/v1/translations/{locale}",
     *      tags={"Translations"},
     *      summary="Get i18n translation strings",
     *      description="Returns all translation key-value pairs for the given locale in i18n flat format (namespace::group.key => value).",
     *
     *      @OA\Parameter(
     *          name="locale",
     *          in="path",
     *          required=true,
     *          description="Locale code, e.g. en, vi, ja",
     *
     *          @OA\Schema(type="string", example="en")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *              type="object",
     *
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  description="Flat key-value pairs: namespace::group.key => translated string",
     *                  example={"core::app.save": "Save", "core::app.cancel": "Cancel"}
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(response=500, description="Server error", ref="#/components/responses/error_500")
     * )
     */
    public function index(string $locale): JsonResponse
    {
        $items = $this->buildTranslationCollection($locale);

        return $this->restSuccess($this->formatAsI18n($items, $locale));
    }

    private function buildTranslationCollection(string $locale): Collection
    {
        $enabledModules = $this->getEnabledModuleAliases();
        $themeKey = $this->getCurrentThemeKey();

        $collection = $this->translationManager->modules()
            ->filter(
                function ($module, $key) use ($themeKey) {
                    if ($module['type'] === 'module') {
                        return false;
                    }

                    if ($module['type'] === 'theme') {
                        return $themeKey !== null && $key === $themeKey;
                    }

                    return true;
                }
            )
            ->map(
                function ($module, $key) use ($locale) {
                    return $this->translationManager->locale($key)
                        ->translationLines($locale)
                        ->map(function ($item) use ($module) {
                            $item['namespace'] = $module['namespace'] ?? '*';

                            return $item;
                        });
                }
            )
            ->filter(fn ($item) => $item->isNotEmpty())
            ->flatten(1);

        $langs = LanguageLine::get()
            ->keyBy(fn ($item) => "{$item->namespace}-{$item->group}-{$item->key}");

        return $collection->map(
            function ($item) use ($langs, $locale) {
                $dbLine = $langs->get("{$item['namespace']}-{$item['group']}-{$item['key']}");
                $item['trans'] = ($dbLine?->text[$locale] ?? null) ?? $item['trans'];

                return $item;
            }
        );
    }

    private function getEnabledModuleAliases(): array
    {
        try {
            return collect(Module::allEnabled())
                ->map(fn ($m) => method_exists($m, 'getAliasName') ? $m->getAliasName() : null)
                ->filter()
                ->values()
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    private function getCurrentThemeKey(): ?string
    {
        try {
            $theme = Theme::current();

            return method_exists($theme, 'lowerName') ? $theme->lowerName() : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Format the collection into a flat i18n key-value map.
     *
     * Key format rules:
     *  - PHP file strings:  "namespace::group.key"  (e.g. "core::app.save")
     *  - JSON file strings: "namespace::key"         (group = '*', e.g. "core::Hello World")
     *  - Laravel default (namespace='*'):
     *      PHP  → "group.key"   (e.g. "app.title")
     *      JSON → "key"         (e.g. "Hello World")
     *
     * @return array<string, string>
     */
    private function formatAsI18n(Collection $items, string $locale): array
    {
        return $items
            ->mapWithKeys(function ($item) {
                $namespace = $item['namespace'] ?? '*';
                $group = $item['group'] ?? '*';
                $key = $item['key'] ?? '';
                $trans = $item['trans'] ?? '';

                // Build the group prefix (empty when group is the special wildcard '*')
                $groupPrefix = ($group !== '*') ? "{$group}." : '';

                $i18nKey = $namespace !== '*'
                    ? "{$namespace}::{$groupPrefix}{$key}"
                    : "{$groupPrefix}{$key}";

                return [$i18nKey => $trans];
            })
            ->toArray();
    }
}
