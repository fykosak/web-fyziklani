<?php

declare(strict_types=1);

namespace App\Modules\Vyfuk\Core;

use Fykosak\Utils\UI\Navigation\NavItem;
use Fykosak\Utils\UI\PageTitle;

abstract class BasePresenter extends \App\Modules\Core\ContestPresenter
{
    /**
     * @return NavItem[]
     */
    protected function getNavItems(): array
    {
        $items = [];

        $items[] = new NavItem(
            new PageTitle('Zadání', 'fa-solid fa-file-pen'),
            ':Default:Problems:default'
        );

        $items[] = new NavItem(
            new PageTitle('Pořadí', 'fa-solid fa-ranking-star'),
            ':Default:Results:default'
        );

        $items[] = new NavItem(
            new PageTitle('O nás', 'fa-solid fa-users'),
            ':Default:About:default',
            [],
            [
                new NavItem(new PageTitle('Co je Výfuk?'), ':Default:About:default'),
                new NavItem(new PageTitle('Organizátoři'), ':Default:About:organizers'),
                new NavItem(new PageTitle('Historie'), ':Default:About:history'),
                new NavItem(new PageTitle('Podpořte nás'), ':Default:About:sponsors'),
                new NavItem(new PageTitle('Kontakt'), ':Default:About:contact'),
            ],
        );

        $items[] = new NavItem(
            new PageTitle('Jak řešit', 'fa-solid fa-book'),
            ':Default:Section:howToEngage',
            [],
            [
                new NavItem(new PageTitle('Jak se zapojit'), ':Default:Section:howToEngage'),
                new NavItem(new PageTitle('Pravidla'), ':Default:Section:rules'),
                new NavItem(new PageTitle('Jak psát řešení'), ':Default:Section:howToSolve'),
                new NavItem(new PageTitle('Jak psát experimenty'), ':Default:Section:howToExperiment'),
            ],
        );

        $items[] = new NavItem(
            new PageTitle('Akce', 'fa-solid fa-calendar-days'),
            ':Default:Events:'
        );

        $items[] = new NavItem(
            new PageTitle('Pro učitele', 'fa-solid fa-user-graduate'),
            ':Default:Separate:teachers',
        );

        $items[] = new NavItem(
            new PageTitle('Přihlásit se', "icon icon-fksdb"),
            'https://db.fykos.cz'
        );

        return $items;
    }

    protected function localize(): void
    {
        $this->lang = 'cs';
        parent::localize();
    }
    public function getContestId(): int
    {
        return 2;
    }
}
