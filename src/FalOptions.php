<?php

declare(strict_types=1);

namespace AiSdk\Fal;

use AiSdk\Support\Sdk;
use AiSdk\Utils\Support\Env;
use AiSdk\Utils\Support\Url;

/**
 * fal.ai REST API (@ai-sdk/fal).
 */
final class FalOptions
{
    public const DEFAULT_BASE_URL = 'https://fal.run';

    public const PROVIDER_NAME = 'fal';

    /**
     * @param  array<string, string>  $headers
     */
    public function __construct(
        public readonly string $apiKey,
        public readonly string $baseUrl = self::DEFAULT_BASE_URL,
        public readonly array $headers = [],
        public readonly int $transcriptionPollTimeoutMs = 60_000,
        public readonly int $videoPollIntervalMs = 2000,
        public readonly int $videoPollTimeoutMs = 300_000,
        public readonly ?Sdk $sdk = null,
    ) {}

    /**
     * @param  array<string, mixed>  $config
     */
    public static function fromArray(array $config = []): self
    {
        $apiKey = Env::loadApiKey(
            isset($config['apiKey']) ? (string) $config['apiKey'] : null,
            'FAL_API_KEY',
            self::PROVIDER_NAME,
        );

        $baseUrl = Url::withoutTrailingSlash(
            Env::loadOptionalSetting(isset($config['baseUrl']) ? (string) $config['baseUrl'] : null, 'FAL_BASE_URL')
                ?? self::DEFAULT_BASE_URL,
        );

        /** @var array<string, string> $headers */
        $headers = isset($config['headers']) && is_array($config['headers']) ? $config['headers'] : [];

        $tPoll = isset($config['transcriptionPollTimeoutMs']) ? (int) $config['transcriptionPollTimeoutMs'] : 60_000;
        $vInt = isset($config['videoPollIntervalMs']) ? (int) $config['videoPollIntervalMs'] : 2000;
        $vTimeout = isset($config['videoPollTimeoutMs']) ? (int) $config['videoPollTimeoutMs'] : 300_000;

        $sdk = $config['sdk'] ?? null;

        return new self(
            apiKey: $apiKey,
            baseUrl: $baseUrl,
            headers: $headers,
            transcriptionPollTimeoutMs: $tPoll,
            videoPollIntervalMs: $vInt,
            videoPollTimeoutMs: $vTimeout,
            sdk: $sdk instanceof Sdk ? $sdk : null,
        );
    }

    /**
     * @return array<string, string>
     */
    public function authHeaders(): array
    {
        return array_merge([
            'Authorization' => 'Key ' . $this->apiKey,
        ], $this->headers);
    }
}
