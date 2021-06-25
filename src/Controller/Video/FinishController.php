<?php

namespace App\Controller\Video;

use App\Command\Video\FinishCommand;
use App\Serializer\SerializerInterface;
use DateTime;
use DateTimeInterface;

class FinishController
{
    public function __construct(private FinishCommand $command, private SerializerInterface $serializer)
    {
    }

    public function __invoke(int $videoId): void
    {
        $finishedAt = new DateTime();
        $this->command->execute($videoId, $finishedAt);

        echo $this->serializer->serialize(['finishedAt' => $finishedAt->format(DateTimeInterface::ATOM)]);
        http_response_code(200);
    }
}