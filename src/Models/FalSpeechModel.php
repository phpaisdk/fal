<?php

declare(strict_types=1);

namespace AiSdk\Fal\Models;

use AiSdk\Exceptions\InvalidResponseException;
use AiSdk\Contracts\BaseModel;
use AiSdk\Contracts\SpeechModelInterface;
use AiSdk\Fal\FalOptions;
use AiSdk\Requests\SpeechRequest;
use AiSdk\Responses\SpeechResponse;
use AiSdk\Results\AudioData;
use AiSdk\Support\Usage;
use AiSdk\Utils\Support\Url;
use AiSdk\Utils\Http\RemoteFetch;
use Closure;

final class FalSpeechModel extends BaseModel implements SpeechModelInterface
{
    /** @param (Closure(string): list<string>)|null $dnsResolver */
    public function __construct(private readonly string $modelId, private readonly FalOptions $options, private readonly ?Closure $dnsResolver = null) {}
    public function provider(): string
    {
        return FalOptions::PROVIDER_NAME;
    }
    public function modelId(): string
    {
        return $this->modelId;
    }
    public function generate(SpeechRequest $request): SpeechResponse
    {
        $payload = $this->runner($this->options->sdk)->postJson(Url::joinPath($this->options->baseUrl, '/' . $this->modelId), array_replace(['text' => $request->input, 'voice' => $request->voice], $request->providerOptionsFor($this->provider())), $this->options->authHeaders(), $this->provider());
        $url = $payload['audio']['url'] ?? null;
        if (!is_string($url) || $url === '') {
            throw new InvalidResponseException('fal returned no audio URL.');
        }
        $sdk = $this->options->sdk ?? \AiSdk\Generate::sdk();
        $response = RemoteFetch::bytes($sdk, $url, dnsResolver: $this->dnsResolver);
        return new SpeechResponse(new AudioData($response->body, (string) ($payload['audio']['content_type'] ?? 'audio/mpeg')), Usage::empty(), $payload, [$this->provider() => ['url' => $url]]);
    }
}
