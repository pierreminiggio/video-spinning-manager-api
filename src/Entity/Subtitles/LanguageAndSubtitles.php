<?php

namespace App\Entity\Subtitles;

class LanguageAndSubtitles
{
    /**
     * @param Subtitles[] $subtitles
     */
    public function __construct(
        private string $language,
        private array $subtitles
    )
    {
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @return Subtitles[]
     */
    public function getSubtitles(): array
    {
        return $this->subtitles;
    }
}
