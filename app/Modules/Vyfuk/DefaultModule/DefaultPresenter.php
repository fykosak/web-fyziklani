<?php

declare(strict_types=1);

namespace App\Modules\Vyfuk\DefaultModule;

use App\Models\Downloader\ProblemService;
use App\Models\Downloader\EventService;
use Fykosak\FKSDBDownloaderCore\Requests\SeriesResultsRequest;
use Nette\Application\Responses\FileResponse;
use Nette\DI\Container;
use Nette\Utils\Image;

class DefaultPresenter extends BasePresenter
{
    private readonly ProblemService $problemService;

    /** @persistent */
    public ?int $year = null;
    /** @persistent */
    public ?int $series = null;

    public function injectServiceProblem(ProblemService $problemService): void
    {
        $this->problemService = $problemService;
    }

    protected EventService $eventService;

    public function injectEventServicesAndCache(EventService $eventService): void
    {
        $this->eventService = $eventService;
    }

    private readonly string $wwwDir;

    public function injectWwwDir(Container $container): void
    {
        $this->wwwDir = $container->getParameters()['wwwDir'];
    }

    public function renderDefault(): void
    {
        $this->template->newsList = $this->loadNews();

        $year = $this->year ?? $this->getCurrentYear()->year;
        $series = $this->series ?? $this->problemService->getLatestSeries('vyfuk', $year);
        $series = $this->problemService->getSeries('vyfuk', $year, $series);
        $this->template->series = $series;

        $previousSeries = $this->problemService->getSeries('vyfuk', $year, $this->problemService->getLatestSeries('vyfuk', $year) - 1);
        $this->template->previousSeries = $previousSeries;

        $this->template->checkAllSolutions = $this->checkAllSolutions($previousSeries, $this->lang);

        $this->template->resultsReady = $this->resultsReady($year, $previousSeries);

        $this->template->nearestEvent = $this->eventService->getNewest([10, 11, 12, 18]);
    }

    public function checkAllSolutions($series, $lang): bool
    {
        $problems = [];
        foreach ($series->problems as $probNum) {
            $problem = $this->problemService->getProblem('vyfuk', $series->year, $series->series, $probNum);
            $problems[] = $problem;
        }

        return array_reduce($problems, function ($carry, $problem) use ($lang) {
            return $carry && $this->problemService->getSolution($problem, $lang) !== null;
        }, true);
    }

    public function resultsReady($year, $series): bool
    {
        $results = $this->downloader->download(new SeriesResultsRequest($this->getContestId(), $year));

        if (isset($results['tasks']['VYFUK_6'][$series->series])) {
            return true;
        } else {
            return false;
        }
    }

    public function loadNews(): array
    {
        $json = file_get_contents(__DIR__ . '/templates/Default/news.json');
        $newsList = json_decode($json, true);

        return $newsList;
    }

    public function renderPreview(string $path): void
    {
        $basepath = realpath($this->wwwDir . '/media');
        $path = realpath($basepath . '/' . $path);
        if ($path === false || strpos($path, $basepath . '/') !== 0) {
            $this->error();
        }
        $path = substr($path, strlen($basepath));
        if (!is_file($basepath . '/preview/' . $path)) {
            $img = Image::fromFile($basepath . '/' . $path);
            if (!is_dir(dirname($basepath . '/preview/' . $path))) {
                mkdir(dirname($basepath . '/preview/' . $path), recursive: true);
            }
            $img->resize(1024, 1024)->save($basepath . '/preview/' . $path);
        }
        $this->sendResponse(new FileResponse($basepath . '/preview/' . $path));
    }
}
