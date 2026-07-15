<?php

declare(strict_types=1);

namespace AiSdk\Fal\Models;

use AiSdk\Contracts\BaseModel;
use AiSdk\Contracts\TranscriptionModelInterface;
use AiSdk\Fal\FalOptions;
use AiSdk\Requests\TranscriptionRequest;
use AiSdk\Responses\TranscriptionResponse;
use AiSdk\Results\TranscriptData;
use AiSdk\Support\Usage;
use AiSdk\Utils\Support\Url;

final class FalTranscriptionModel extends BaseModel implements TranscriptionModelInterface
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
    public function transcribe(TranscriptionRequest $request): TranscriptionResponse
    {
        $audio = $request->audio;
        $payload = $this->runner($this->options->sdk)->postJson(Url::joinPath($this->options->baseUrl, '/' . $this->modelId), array_replace(['audio_url' => $audio->source() === \AiSdk\ContentSource::Url ? $audio->url() : $audio->dataUri()], $request->providerOptionsFor($this->provider())), $this->options->authHeaders(), $this->provider());
        return new TranscriptionResponse(new TranscriptData((string) ($payload['text'] ?? '')), Usage::empty(), $payload, [$this->provider() => []]);
    }
}
