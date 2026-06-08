<?php

declare(strict_types=1);

namespace App\Shared\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\MediaType;
use ApiPlatform\OpenApi\OpenApi;
use ArrayObject;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

/**
 * Decorates the OpenAPI factory to wrap standard JSON collection responses into a custom
 * {"data": [...], "meta": {...}}
 * structure for endpoints marked with the 'x-paginated-collection-json' extension property.
 */
#[AsDecorator('api_platform.openapi.factory')]
final readonly class PaginatedCollectionOpenApiDecorator implements OpenApiFactoryInterface
{
    public function __construct(
        private OpenApiFactoryInterface $decorated
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);
        $paths = $openApi->getPaths();

        foreach ($paths->getPaths() as $path => $pathItem) {
            $getOperation = $pathItem->getGet();

            if (!$getOperation || !($getOperation->getExtensionProperties()['x-paginated-collection-json'] ?? false)) {
                continue;
            }

            $responses = $getOperation->getResponses();
            $okResponse = $responses['200'] ?? null;

            if ($okResponse) {
                $content = $okResponse->getContent();

                if (isset($content['application/json'])) {
                    /** @var MediaType $mediaType */
                    $mediaType = $content['application/json'];

                    $schema = $mediaType->getSchema();

                    $originalRef = $schema['items']['$ref'] ?? null;

                    if ($originalRef) {
                        $newSchema = new ArrayObject([
                            'type' => 'object',
                            'properties' => [
                                'data' => [
                                    'type' => 'array',
                                    'items' => ['$ref' => $originalRef]
                                ],
                                'meta' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'totalItems' => ['type' => 'integer', 'example' => 6],
                                        'currentPage' => ['type' => 'integer', 'example' => 1],
                                        'itemsPerPage' => ['type' => 'integer', 'example' => 10],
                                    ]
                                ]
                            ]
                        ]);

                        $content['application/json'] = $mediaType->withSchema($newSchema);

                        $responses['200'] = $okResponse->withContent($content);
                        $getOperation = $getOperation->withResponses($responses);
                        $paths->addPath($path, $pathItem->withGet($getOperation));
                    }
                }
            }
        }

        return $openApi;
    }
}
