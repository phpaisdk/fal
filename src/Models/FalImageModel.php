<?php

declare(strict_types=1);

namespace AiSdk\Fal\Models;

use AiSdk\Contracts\BaseModel;
use AiSdk\Contracts\ImageModelInterface;
use AiSdk\Exceptions\InvalidResponseException;
use AiSdk\Fal\FalOptions;
use AiSdk\Requests\ImageRequest;
use AiSdk\Responses\ImageResponse;
use AiSdk\Results\ImageData;
use AiSdk\Support\Usage;
use AiSdk\Utils\Support\Url;

final class FalImageModel extends BaseModel implements ImageModelInterface
{
    public function __construct(private readonly string $modelId, private readonly FalOptions $options) {}
    public function provider(): string
    {
        return FalOptions::PROVIDER_NAME;
    }
    public function modelId(): string
    {
        return $this->modelId;
    }
    public function generate(ImageRequest $request): ImageResponse
    {
        $body = array_replace(['prompt' => $request->prompt, 'num_images' => $request->count], $request->size ? ['image_size' => $request->size] : [], $request->aspectRatio ? ['aspect_ratio' => $request->aspectRatio] : [], $request->seed !== null ? ['seed' => $request->seed] : [], $request->providerOptionsFor($this->provider()));
        $payload = $this->runner($this->options->sdk)->postJson(Url::joinPath($this->options->baseUrl, '/' . $this->modelId), $body, $this->options->authHeaders(), $this->provider());
        $images = array_values(array_filter(array_map(fn($image) => is_array($image) && is_string($image['url'] ?? null) ? new ImageData(url: $image['url'], mimeType: (string) ($image['content_type'] ?? 'image/png')) : null, $payload['images'] ?? [])));
        if ($images === []) {
            throw InvalidResponseException::forProvider($this->provider(), 'fal returned no images.', ['body' => $payload]);
        }
        return new ImageResponse($images, Usage::empty(), $payload, [$this->provider() => ['model' => $this->modelId]]);
    }
}
