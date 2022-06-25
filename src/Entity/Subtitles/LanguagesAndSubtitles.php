<?php

namespace App\Entity\Subtitles;

class LanguagesAndSubtitles
{
    /**
     * @param LanguageAndSubtitles[] $languagesAndSubtitles
     */
    public function __construct(
        private array $languagesAndSubtitles
    )
    {}

    /**
     * @return LanguageAndSubtitles[]
     */
    public function getLanguagesAndSubtitles(): array
    {
        return $this->languagesAndSubtitles;
    }
}
