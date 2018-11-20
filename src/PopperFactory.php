<?php
namespace Uspdev\Popper;

use \Uspdev\Nfe\NfeWsConsumer;
use \Uspdev\Popper\Nfe;
use \Uspdev\Popper\Popper;
use \PhpMimeMailParser\Parser;

class PopperFactory
{
    public static function create($cfg)
    {
        $nfews = $cfg['nfews'];

        $popper = new Popper($cfg);
        $nfe = new Nfe($cfg);
        $popper->setNfe($nfe); // injection ??

        $sefaz = new NfeWsConsumer($nfews['srv'], $nfews['usr'], $nfews['pwd']);
        $nfe->setSefaz($sefaz);

        $parser = new Parser();
        $popper->setParser($parser);

        return $popper;
    }
}
