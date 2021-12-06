<?php

declare(strict_types=1);

namespace Yiisoft\Swagger\Service;

use InvalidArgumentException;
use OpenApi\Generator;
use OpenApi\Processors\MergeIntoOpenApi;
use OpenApi\Util;
use OpenApi\Annotations\OpenApi;
use RuntimeException;
use Yiisoft\Aliases\Aliases;

use function array_map;
use function dirname;
use function sprintf;

final class SwaggerService
{
    private Aliases $aliases;

    private string $viewPath;
    private string $viewName;

    public function __construct(Aliases $aliases)
    {
        $this->aliases = $aliases;
        $this->setupDefaults();
    }

    private function setupDefaults(): void
    {
        $this->viewPath = dirname(__DIR__, 2) . '/views';
        $this->viewName = 'swagger-ui';
    }

    public function getViewPath(): string
    {
        return $this->aliases->get($this->viewPath);
    }

    public function getViewName(): string
    {
        return $this->viewName;
    }

    public function fetch(array $annotationPaths): OpenApi
    {
        if ($annotationPaths === []) {
            throw new InvalidArgumentException('Annotation paths cannot be empty array.');
        }

        $directories = array_map(fn (string $path): string => $this->aliases->get($path), $annotationPaths);
        $openApi = Generator::scan(Util::finder($directories));

        if ($openApi === null) {
            throw new RuntimeException(sprintf(
                'No OpenApi target set. Run the "%s" processor before "%s::fetch()".',
                MergeIntoOpenApi::class,
                self::class,
            ));
        }

        return $openApi;
    }
}
