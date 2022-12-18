<?php

namespace Unit;

use PhpSimple\HtmlDomParser;
use PHPUnit\Framework\TestCase;

class HtmlDomParserTest extends TestCase
{

    public function testStrGetHtml(): void
    {
        $str = '<div id="div1">';
        $str .= '<div id="div10"></div>';
        $str .= '<div id="div11"></div>';
        $str .= '<div id="div12"></div>';
        $str .= '</div>';

        $htmlDomParser = new HtmlDomParser();
        $dom = $htmlDomParser->strGetHtml($str);

        self::assertEquals($str, (string)$dom);
        $e = $dom->find('div#div1', 0);
        self::assertEquals('div10', $e->firstChild()->getAttribute('id'));
        self::assertEquals('div12', $e->lastChild()->getAttribute('id'));
        self::assertNull($e->nextSibling());
        self::assertNull($e->previousSibling());
    }

    public function testFileGetHtml(): void
    {
        $htmlDomParser = new HtmlDomParser();
        $dom = $htmlDomParser->fileGetHtml(__DIR__.'/data/test-file.html');

        $e = $dom->find('div#div1', 0);
        self::assertEquals('div10', $e->firstChild()->getAttribute('id'));
        self::assertEquals('div12', $e->lastChild()->getAttribute('id'));
        self::assertNull($e->nextSibling());
        self::assertNull($e->previousSibling());
    }
}