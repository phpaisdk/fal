<?php

declare(strict_types=1);

namespace AiSdk\Fal;

use AiSdk\Contracts\BaseProvider;
use AiSdk\Contracts\ImageModelInterface;
use AiSdk\Contracts\ImageProviderInterface;
use AiSdk\Contracts\SpeechModelInterface;
use AiSdk\Contracts\SpeechProviderInterface;
use AiSdk\Contracts\TranscriptionModelInterface;
use AiSdk\Contracts\TranscriptionProviderInterface;
use AiSdk\Contracts\VideoModelInterface;
use AiSdk\Contracts\VideoProviderInterface;
use AiSdk\Fal\Models\FalImageModel;
use AiSdk\Fal\Models\FalSpeechModel;
use AiSdk\Fal\Models\FalTranscriptionModel;
use AiSdk\Fal\Models\FalVideoModel;

final class FalProvider extends BaseProvider implements ImageProviderInterface, SpeechProviderInterface, TranscriptionProviderInterface, VideoProviderInterface
{
    public function __construct(public readonly FalOptions $options) {}

    public function name(): string
    {
        return FalOptions::PROVIDER_NAME;
    }

    protected function imageModel(string $modelId): ImageModelInterface
    {
        return new FalImageModel($modelId, $this->options);
    }

    protected function speechModel(string $modelId): SpeechModelInterface
    {
        return new FalSpeechModel($modelId, $this->options);
    }

    protected function transcriptionModel(string $modelId): TranscriptionModelInterface
    {
        return new FalTranscriptionModel($modelId, $this->options);
    }

    protected function videoModel(string $modelId): VideoModelInterface
    {
        return new FalVideoModel($modelId, $this->options);
    }

}
