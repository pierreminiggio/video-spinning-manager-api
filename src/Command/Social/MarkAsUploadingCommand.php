<?php

namespace App\Command\Social;

use App\Enum\UploadTypeEnum;
use PierreMiniggio\DatabaseFetcher\DatabaseFetcher;

class MarkAsUploadingCommand
{
    public function __construct(private DatabaseFetcher $fetcher)
    {
    }

    /**
     * @see UploadTypeEnum for $uploadType
     */
    public function execute(string $uploadType, int $uploadId): void
    {
        $this->fetcher->exec(
            $this->fetcher->createQuery(
                'spinned_content_upload_status'
            )->insertInto(
                'upload_type, upload_id',
                ':upload_type, :upload_id'
            ),
            ['upload_type' => $uploadType, 'upload_id' => $uploadId]
        );
    }
}
