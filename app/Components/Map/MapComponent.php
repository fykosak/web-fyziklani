<?php

declare(strict_types=1);

namespace App\Components\Map;

use App\Models\GamePhaseCalculator;
use Fykosak\NetteFKSDBDownloader\ORM\Services\ServiceEventDetail;
use Fykosak\Utils\BaseComponent\BaseComponent;
use Nette\DI\Container;

class MapComponent extends BaseComponent
{
    private static $uniqueId = 0;

    protected ServiceEventDetail $serviceTeam;
    protected int $forEventId;

    protected int $teamCount;
    protected array $teamCountries;

    protected GamePhaseCalculator $gamePhaseCalculator;

    public function injectServiceTeam(ServiceEventDetail $serviceTeam): void
    {
        $this->serviceTeam = $serviceTeam;
    }

    public function __construct(Container $container, GamePhaseCalculator $calculator)
    {
        parent::__construct($container);
        $this->forEventId = $calculator->getFKSDBEvent()->eventId;
        $this->gamePhaseCalculator = $calculator;
    }

    /**
     * @throws \Throwable
     */
    public function processTeams(): void
    {
        $this->teamCount = 0;
        $this->teamCountries = [];

        foreach ($this->serviceTeam->getTeams($this->forEventId) as $team) {
            if (!in_array($team->status, ["participated", "disqualified", "applied", "pending", "approved"])) {
                continue;
            }
            $this->teamCount++;
            foreach ($team->members as $member) {
                if (is_null($member->countryIso)) {
                    continue;
                }
                if (
                    !in_array($member->countryIso, $this->teamCountries) &&
                    strtolower($member->countryIso) !== 'zz'
                ) {
                    $this->teamCountries[] = $member->countryIso;
                }
            }
        }
    }

    /**
     * @throws \Throwable
     */
    public function render($inverseColors = false): void
    {
        $this->processTeams();

        $this->template->teamCount = $this->teamCount;
        $this->template->teamCountries = $this->teamCountries;

        $this->template->uniqueId = self::$uniqueId++;
        $this->template->inverseColors = $inverseColors;

        $this->template->lang = $this->getPresenter()->lang;
        $this->template->gamePhaseCalculator = $this->gamePhaseCalculator;
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'map.latte');
    }
}
