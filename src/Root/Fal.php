<?php

declare(strict_types=1);

namespace AiSdk;

use AiSdk\Contracts\Model;
use AiSdk\Fal\FalOptions;
use AiSdk\Fal\FalProvider;

/**
 * fal.ai — image, speech, transcription, and video models (@ai-sdk/fal).
 */
final class Fal
{
    private static ?FalProvider $default = null;

    /**
     * @param  array<string, mixed>  $config
     */
    public static function create(array $config = []): FalProvider
    {
        $provider = new FalProvider(FalOptions::fromArray($config));
        self::$default = $provider;

        return $provider;
    }

    public static function default(): FalProvider
    {
        return self::$default ??= self::create();
    }

    public static function reset(): void
    {
        self::$default = null;
    }

    public static function model(string $modelId): Model
    {
        return self::default()->model($modelId);
    }
}
