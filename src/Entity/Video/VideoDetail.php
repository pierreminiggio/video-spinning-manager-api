<?php

namespace App\Entity\Video;

use App\Entity\Account\AccountCollection;

class VideoDetail
{
    /**
     * @param int[] $planifiedToBePostedOnAccounts
     */
    public function __construct(
        public Video $video,
        public bool $downloaded,
        public bool $hasRenderedPreview,
        public EditorState $editorState,
        public AccountCollection $spinnedAccountSocialMediasAccounts,
        public array $planifiedToBePostedOnAccounts
    )
    {
    }
}
