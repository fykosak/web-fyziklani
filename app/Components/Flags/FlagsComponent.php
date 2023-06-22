<?php

declare(strict_types=1);

namespace App\Components\Flags;

use Fykosak\NetteFKSDBDownloader\ORM\Models\ModelParticipant;
use Fykosak\NetteFKSDBDownloader\ORM\Models\ModelTeam;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;

class FlagsComponent extends BaseComponent
{
    public function getFlagForParticipant(ModelParticipant $participant): string
    {
        return $participant->countryIso;
    }

    public function getFlagsForTeam(ModelTeam $team): array
    {
        $flags = [];
        foreach ($team->participants as $participant) {
            $flags[] = $this->getFlagForParticipant($participant);
        }
        return $flags;
    }

    public function getUniqueFlagsForTeam(ModelTeam $team): array
    {
        return array_unique($this->getFlagsForTeam($team));
    }

    public function renderFlag(ModelParticipant $participant): void
    {
        $this->template->flags = [$this->getFlagForParticipant($participant)];
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'flags.latte');
    }

    public function renderFlags(ModelTeam $team): void
    {
        $this->template->flags = $this->getFlagsForTeam($team);
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'flags.latte');
    }

    public function renderUniqueFlags(ModelTeam $team): void
    {
        $this->template->flags = $this->getUniqueFlagsForTeam($team);
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'flags.latte');
    }
}
