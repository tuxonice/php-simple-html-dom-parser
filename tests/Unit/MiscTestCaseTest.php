<?php

namespace Unit;

use PhpSimple\SimpleHtmlDom;
use PHPUnit\Framework\TestCase;

class MiscTestCaseTest extends TestCase
{

    public function testProblemOfLastElemenNotFound(): void
    {
        $dom = new SimpleHtmlDom();
        $str = <<<HTML
<img class="class0" id="id0" src="src0">
<img class="class1" id="id1" src="src1">
<img class="class2" id="id2" src="src2">
HTML;

        $dom->load($str);
        $es = $dom->find('img');
        self::assertCount(3, $es);
        self::assertEquals('src0', $es[0]->src);
        self::assertEquals('src1', $es[1]->src);
        self::assertEquals('src2', $es[2]->src);
        self::assertEmpty($es[0]->innertext);
        self::assertEmpty($es[1]->innertext);
        self::assertEmpty($es[2]->innertext);
        self::assertEquals('<img class="class0" id="id0" src="src0">', $es[0]->outertext);
        self::assertEquals('<img class="class1" id="id1" src="src1">', $es[1]->outertext);
        self::assertEquals('<img class="class2" id="id2" src="src2">', $es[2]->outertext);
        self::assertEquals('src0', $dom->find('img', 0)->src);
        self::assertEquals('src1', $dom->find('img', 1)->src);
        self::assertEquals('src2', $dom->find('img', 2)->src);
        self::assertNull($dom->find('img', 3));
        self::assertNull($dom->find('img', 99));
    }

    public function testErrorTag(): void
    {
        $str = <<<HTML
<img class="class0" id="id0" src="src0"><p>p1</p>
<img class="class1" id="id1" src="src1"><p>
<img class="class2" id="id2" src="src2"></a></div>
HTML;

        $dom = new SimpleHtmlDom();
        $dom->load($str);
        $es = $dom->find('img');
        self::assertCount(3, $es);
        self::assertEquals('src0', $es[0]->src);
        self::assertEquals('src1', $es[1]->src);
        self::assertEquals('src2', $es[2]->src);

        $es = $dom->find('p');
        self::assertEquals('p1', $es[0]->innertext);
    }
}