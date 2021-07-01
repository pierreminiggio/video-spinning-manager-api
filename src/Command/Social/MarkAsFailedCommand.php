<?php

namespace App\Command\Social;

use App\Enum\UploadTypeEnum;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class MarkAsFailedCommand
{
    public function __construct(private DatabaseFetcher $fetcher)
    {
    }

    /**
     * @see UploadTypeEnum for $uploadType
     */
    public function execute(string $uploadType, int $uploadId, string $failReason): void
    {
        $this->fetcher->exec(
            $this->fetcher->createQuery(
                'spinned_content_upload_status'
            )->update(
                'failed_at = NOW(), fail_reason = :fail_reason',
            )->where(
                'upload_type = :upload_type AND upload_id = :upload_id'
            ),
            ['upload_type' => $uploadType, 'upload_id' => $uploadId, 'fail_reason' => $failReason]
        );
    }
}
