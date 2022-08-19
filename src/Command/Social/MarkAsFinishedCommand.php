<?php

namespace App\Command\Social;

use App\Enum\UploadTypeEnum;
use Exception;
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
        $fetchedUploadStatuses = $this->fetcher->query(
            $this->fetcher->createQuery(
                'spinned_content_upload_status'
            )->select(
                'id'
            )->where(
                'upload_type = :upload_type AND upload_id = :upload_id'
            )->orderBy(
                'id', 'desc'
            )->limit(1),
            ['upload_type' => $uploadType, 'upload_id' => $uploadId]
        );
        
        if (! $fetchedUploadStatuses) {
            throw new Exception('no current status for ' . $uploadType . ' ' . $uploadId);
        }
        
        $statusId = $fetchedUploadStatuses[0]['id'];
        
        $this->fetcher->exec(
            $this->fetcher->createQuery(
                'spinned_content_upload_status'
            )->update(
                'finished_at = NOW(), remote_url = :remote_url',
            )->where(
                'id = :id'
            ),
            ['id' => $statusId, 'remote_url' => $remoteUrl]
        );
    }
}
