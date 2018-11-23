<?php
namespace Uspdev\GerenciadorNfe;

use \Uspdev\Nfe\NfeWsConsumer;
use \Uspdev\GerenciadorNfe\Nfe;
use \Uspdev\GerenciadorNfe\GerenciadorNfe;
use \PhpMimeMailParser\Parser;

class EmailFactory
{
    public static function create($cfg)
    {
        $nfews = $cfg['nfews'];

        $popper = new Email($cfg);
        $nfe = new Nfe($cfg);
        $popper->setNfe($nfe); // injection ??

        $sefaz = new NfeWsConsumer($nfews['srv'], $nfews['usr'], $nfews['pwd']);
        $nfe->setSefaz($sefaz);

        $parser = new Parser();
        $popper->setParser($parser);

        return $popper;
    }
}
