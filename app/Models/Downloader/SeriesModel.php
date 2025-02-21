<?php

declare(strict_types=1);

namespace App\Models\Downloader;

use DateTime;

class SeriesModel
{
    public ?string $deadline;
    public int $year;
    public int $series;
    /**
     * @var int[]
     */
    public array $problems;
    /**
     * @var string[]
     */
    public ?array $serialTopic = null;

    /**
     * @throws \Exception
     */
    public function getDeadline(): ?DateTime
    {
        if ($this->deadline) {
            return new DateTime($this->deadline);
        }
        return null;
    }

    public function getSerialTopic($contest, $lang): ?string
    {
        if ($this->serialTopic) {
            return $this->serialTopic[$lang];
        }

        $defaultTopic = match ($contest) {
            'vyfuk' => [
                'cs' => "$this->series. Výfučtení",
                'en' => ''
            ],
            default => [
                'cs' => "$this->series. seriál",
                'en' => "Serial Number $this->series"
            ]
        };

        return $defaultTopic[$lang];
    }
}
