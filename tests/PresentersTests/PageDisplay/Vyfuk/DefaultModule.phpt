<?php

// phpcs:disable
declare(strict_types=1);

namespace Tests\PresentersTests\PageDisplay\Vyfuk;

use Tests\PresentersTests\PageDisplay\AbstractPageDisplayTestCase;

define('MODULE_NAME', 'vyfuk');
$container = require '../../../Bootstrap.php';

class DefaultModule extends AbstractPageDisplayTestCase
{
    public function getPages(): array
    {
        return self::getPageLangVariants([
            // tasks
            // ['Default:Problems', 'default'],
            ['Default:Problems', 'bingo'],
            // results
            ['Default:Results', 'default'],
            // about us
            ['Default:About', 'default'],
            ['Default:About', 'organizers'],
            ['Default:About', 'history'],
            ['Default:About', 'sponsors'],
            ['Default:About', 'contact'],
            // how to engage
            ['Default:Section', 'howToEngage'],
            ['Default:Section', 'rules'],
            ['Default:Section', 'howToSolve'],
            ['Default:Section', 'howToExperiment'],
            // events
            ['Default:Events', 'default'],
            ['Default:Events', 'dalsi'],
            // ['Default:Events', 'detail'],
            ['Default:Events', 'setkani'],
            ['Default:Events', 'tabor'],
            ['Default:Events', 'vikendovka'],
            // results
            ['Default:Results', 'default'],
            // separate pages
            ['Default:Separate', 'teachers'],
            // ['Default:Separate', 'serialArchive'],
            // titlepage
            // ['Default:Default', 'default'],
        ]);
    }
}

$testCase = new DefaultModule($container);
$testCase->run();
// phpcs:enable
