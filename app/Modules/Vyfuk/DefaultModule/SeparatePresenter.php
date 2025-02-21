<?php

declare(strict_types=1);

namespace App\Modules\Vyfuk\DefaultModule;

use App\Models\Downloader\ProblemService;
use Throwable;

class SeparatePresenter extends BasePresenter
{
    private readonly ProblemService $problemService;

    public function injectServiceProblem(ProblemService $problemService): void
    {
        $this->problemService = $problemService;
    }

    /**
     * @throws \Throwable
     */
    public function renderSerialArchive(): void
    {
        $this->template->problemService = $this->problemService;

        $this->template->yearsAndSeries = $this->getYearsAndSeries();

        $this->template->hasAtLeastOneSerial = $this->checkYearsSerials($this->getYearsAndSeries());
    }

    private function getYearsAndSeries(): array
    {

        error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

        $yearsAndSeries = [];
        foreach ($this->getContest()->years as $year) {
            try {
                $yearJson = $this->problemService->getYearJson('vyfuk', $year->year);
                $availableSeriesNumbers = array_keys($yearJson);
                $yearsAndSeries[$year->year] = $availableSeriesNumbers;
            } catch (Throwable $e) {
                continue;
            }
        }

        // sort in decreasing order by key
        krsort($yearsAndSeries);

        return $yearsAndSeries;
    }

    private function checkYearsSerials($yearsAndSeries): array
    {
        $hasAtLeastOneSerial = [];
        foreach ($yearsAndSeries as $year => $seriesList) {
            $hasAtLeastOneSerial[$year] = false;
            foreach ($seriesList as $seriesNo) {
                $series = $this->problemService->getSeries('vyfuk', $year, $seriesNo);
                $serialPath = $this->problemService->getSerial('vyfuk', $series, $this->lang);
                if ($serialPath) {
                    $hasAtLeastOneSerial[$year] = true;
                    break;
                }
            }
        }

        return $hasAtLeastOneSerial;
    }
}
