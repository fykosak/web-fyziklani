<?php

declare(strict_types=1);

namespace App\Modules\Fof\ArchiveModule;

use App\Components\TeamResults\TeamResultsComponent;
use Fykosak\Utils\UI\PageTitle;
use Nette\Application\BadRequestException;

class ResultsPresenter extends BasePresenter
{

    public function renderDefault(): void
    {
        $this->setPageTitle(new PageTitle(null, $this->csen('Výsledky', 'Results')));
    }

    /**
     * @throws BadRequestException
     * @throws \Throwable
     */
    protected function createComponentTeamResults(): TeamResultsComponent
    {
        if ($this->getEvent()->eventId == 180) throw new Error("oops");
        return new TeamResultsComponent($this->getContext(), $this->getEvent()->eventId);
    }
}
