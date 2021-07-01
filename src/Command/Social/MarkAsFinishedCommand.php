<?php

namespace App\Command\Social;

use App\Enum\UploadTypeEnum;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class MarkAsFinishedCommand
{
    public function __construct(private DatabaseFetcher $fetcher)
    {
    }

    /**
     * @see UploadTypeEnum for $uploadType
     */
    public function execute(string $uploadType, int $uploadId, string $remoteUrl): void
    {
        $this->fetcher->exec(
            $this->fetcher->createQuery(
                'spinned_content_upload_status'
            )->update(
                'finished_at = NOW(), remote_url = :remote_url',
            )->where(
                'upload_type = :upload_type AND upload_id = :upload_id'
            ),
            ['upload_type' => $uploadType, 'upload_id' => $uploadId, 'remote_url' => $remoteUrl]
        );
    }
}
