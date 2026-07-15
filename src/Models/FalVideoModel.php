<?php

declare(strict_types=1);

namespace AiSdk\Fal\Models;

use AiSdk\ContentSource;
use AiSdk\Contracts\BaseModel;
use AiSdk\Contracts\VideoModelInterface;
use AiSdk\Fal\FalOptions;
use AiSdk\Requests\VideoRequest;
use AiSdk\Responses\VideoJob;
use AiSdk\Responses\VideoJobStatus;
use AiSdk\Results\VideoData;
use AiSdk\Support\Usage;
use AiSdk\Utils\Support\Url;

final class FalVideoModel extends BaseModel implements VideoModelInterface
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
    public function generate(VideoRequest $request): VideoJob
    {
        $body = array_replace(['prompt' => $request->prompt], $request->image?->source() === ContentSource::Url ? ['image_url' => $request->image->url()] : [], $request->providerOptionsFor($this->provider()));
        $payload = $this->runner($this->options->sdk)->postJson(Url::joinPath('https://queue.fal.run', '/' . $this->modelId), $body, $this->options->authHeaders(), $this->provider());
        return new VideoJob((string) ($payload['request_id'] ?? ''), $this->provider(), $this->modelId, VideoJobStatus::Queued, rawResponse: $payload, providerMetadata: [$this->provider() => ['response_url' => $payload['response_url'] ?? null]]);
    }
    public function poll(VideoJob $job): VideoJob
    {
        $url = $job->providerMetadata[$this->provider()]['response_url'] ?? null;
        if (!is_string($url) || $url === '') {
            return new VideoJob($job->id, $job->provider, $job->modelId, VideoJobStatus::Failed, errorMessage: 'fal queue response did not include response_url.', rawResponse: $job->rawResponse, providerMetadata: $job->providerMetadata);
        }
        $payload = $this->runner($this->options->sdk)->getJson($url, $this->options->authHeaders(), $this->provider());
        $video = $payload['video']['url'] ?? null;
        return is_string($video) ? new VideoJob($job->id, $job->provider, $job->modelId, VideoJobStatus::Succeeded, new VideoData(url: $video, mimeType: (string) ($payload['video']['content_type'] ?? 'video/mp4')), usage: Usage::empty(), rawResponse: $payload, providerMetadata: $job->providerMetadata) : new VideoJob($job->id, $job->provider, $job->modelId, VideoJobStatus::Running, rawResponse: $payload, providerMetadata: $job->providerMetadata);
    }
}
