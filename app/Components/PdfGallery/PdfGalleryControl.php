<?php

declare(strict_types=1);

namespace App\Components\PdfGallery;

use Fykosak\Utils\Components\DIComponent;
use Nette\Caching\Cache;
use Nette\Caching\Storage;
use Nette\DI\Container;
use Nette\Utils\Finder;
use Nette\Utils\UnknownImageFileException;

class PdfGalleryControl extends DIComponent
{
    private readonly string $wwwDir;
    private readonly Cache $cache;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->wwwDir = $container->getParameters()['wwwDir'];
    }

    public function injectStorage(Storage $storage): void
    {
        $this->cache = new Cache($storage, __NAMESPACE__);
    }

    public static function getPdfs(string $path, string $wwwDir): array
    {
        $pdfs = [];
        try {
            $iterator = Finder::findFiles('*.pdf')->in($wwwDir . $path)->getIterator();
        } catch (\Exception $e) {
            return [];
        }

        foreach ($iterator as $file) {
            $name = $file->getBasename('.pdf');
            // if the name is urlencoded decode it
            if (1 !== preg_match('/[^a-zA-Z0-9+%-_.]/', $name)) {
                $name = urldecode($name);
            }
            $wwwPath = substr($file->getPathname(), strlen($wwwDir));
            $pdfs[] = [
                'src' => $wwwPath,
                'name' => $name,
            ];
        }

        //sort alphabetically
        usort($pdfs, function ($a, $b) {
            return $a['name'] <=> $b['name'];
        });

        foreach ($pdfs as $index => &$pdfFile) {
            $pdfFile['index'] = $index;
        }

        return $pdfs;
    }

    /**
     * @throws UnknownImageFileException
     * @throws \Throwable
     */
    public function render(string $path): void
    {
        $this->template->pdfs = $this->cache->load(
            [$path, $this->wwwDir],
            fn() => self::getPdfs($path, $this->wwwDir)
        );
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'pdfGalleryList.latte');
    }
    public function renderButtons(string $path): void
    {
        $this->template->pdfs = $this->cache->load(
            [$path, $this->wwwDir],
            fn() => self::getPdfs($path, $this->wwwDir)
        );
        $this->template->render(__DIR__ . DIRECTORY_SEPARATOR . 'pdfGalleryButtons.latte');
    }

    /**
     * @throws UnknownImageFileException
     * @throws \Throwable
     */
    public function hasFiles(string $path): bool
    {
        return count($this->cache->load(
            [$path, $this->wwwDir],
            fn() => self::getPdfs($path, $this->wwwDir)
        )) > 0;
    }
}
