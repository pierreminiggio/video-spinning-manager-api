<?php

namespace App\Entity\Video;

use App\Entity\Account\AccountCollection;
use App\Entity\Account\AccountPost;

class VideoDetail
{
    /**
     * @param AccountPost[] $postedOnAccounts
     */
    public function __construct(
        public Video $video,
        public bool $downloaded,
        public bool $hasRenderedPreview,
        public EditorState $editorState,
        public AccountCollection $spinnedAccountSocialMediasAccounts,
        public array $postedOnAccounts
    )
    {
    }
}
