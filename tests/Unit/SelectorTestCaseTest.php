<?php

namespace Unit;

use PhpSimple\SimpleHtmlDom;
use PHPUnit\Framework\TestCase;

class SelectorTestCaseTest extends TestCase
{

    public function testTabAndNewLine(): void
    {
        $dom = new SimpleHtmlDom();
        $str = <<<HTML
<img 
class="class0" id="id0" src="src0">
<img
 class="class1" id="id1" src="src1">
<img class="class2" id="id2" src="src2">
HTML;
        $dom->load($str);
        $e = $dom->find('img');
        self::assertCount(3, $e);
    }

    public function testStdSelector(): void
    {
        $dom = new SimpleHtmlDom();
        $str = <<<HTML
<div>
<img class="class0" id="id0" src="src0">
<img class="class1" id="id1" src="src1">
<img class="class2" id="id2" src="src2">
</div>
HTML;
        $dom->load($str);

        // wildcard
        self::assertCount(1,$dom->find('*'));
        self::assertCount(3, $dom->find('div *'));
        self::assertCount(0,$dom->find('div img *'));

        self::assertCount(1, $dom->find(' * '));
        self::assertCount(3, $dom->find(' div  * '));
        self::assertCount(0,$dom->find(' div  img  *'));

        // tag
        self::assertCount(3, $dom->find('img'));
        self::assertCount(4, $dom->find('text'));

        // class
        $es = $dom->find('img.class0');
        self::assertCount(1, $es);
        self::assertEquals('src0', $es[0]->src);
        self::assertEmpty($es[0]->innertext);
        self::assertEquals('<img class="class0" id="id0" src="src0">', $es[0]->outertext);

        $es = $dom->find('.class0');
        self::assertCount(1, $es);
        self::assertEquals('src0', $es[0]->src);
        self::assertEmpty($es[0]->innertext);
        self::assertEquals('<img class="class0" id="id0" src="src0">', $es[0]->outertext);

        // index
        self::assertEquals('src0', $dom->find('img', 0)->src);
        self::assertEquals('src1', $dom->find('img', 1)->src);
        self::assertEquals('src2', $dom->find('img', 2)->src);
        self::assertEquals('src0', $dom->find('img', -3)->src);
        self::assertEquals('src1', $dom->find('img', -2)->src);
        self::assertEquals('src2', $dom->find('img', -1)->src);

        // id
        $es = $dom->find('img#id1');
        self::assertCount(1, $es);
        self::assertEquals('src1', $es[0]->src);
        self::assertEmpty($es[0]->innertext);
        self::assertEquals('<img class="class1" id="id1" src="src1">', $es[0]->outertext);

        $es = $dom->find('#id2');
        self::assertCount(1, $es);
        self::assertEquals('src2', $es[0]->src);
        self::assertEmpty($es[0]->innertext);
        self::assertEquals('<img class="class2" id="id2" src="src2">', $es[0]->outertext);

        // attr
        $es = $dom->find('img[src="src0"]');
        self::assertCount(1, $es);
        self::assertEquals('src0', $es[0]->src);
        self::assertEmpty($es[0]->innertext);
        self::assertEquals('<img class="class0" id="id0" src="src0">', $es[0]->outertext);

        $es = $dom->find('img[src=src0]');
        self::assertCount(1,$es);
        self::assertEquals('src0', $es[0]->src);
        self::assertEmpty($es[0]->innertext);
        self::assertEquals('<img class="class0" id="id0" src="src0">', $es[0]->outertext);

        // wildcard
        $es = $dom->find('*[src]');
        self::assertCount(3, $es);

        $es = $dom->find('*[src=*]');
        self::assertCount(3, $es);

        $es = $dom->find('*[alt=*]');
        self::assertCount(0, $es);

        $es = $dom->find('*[src="src0"]');
        self::assertCount(1, $es);
        self::assertEquals('src0', $es[0]->src);
        self::assertEmpty($es[0]->innertext);
        self::assertEquals('<img class="class0" id="id0" src="src0">', $es[0]->outertext);

        $es = $dom->find('*[src=src0]');
        self::assertCount(1, $es);
        self::assertEquals('src0', $es[0]->src);
        self::assertEmpty($es[0]->innertext);
        self::assertEquals('<img class="class0" id="id0" src="src0">', $es[0]->outertext);

        $es = $dom->find('[src=src0]');
        self::assertCount(1,$es);
        self::assertEquals('src0', $es[0]->src);
        self::assertEmpty($es[0]->innertext);
        self::assertEquals('<img class="class0" id="id0" src="src0">', $es[0]->outertext);

        $es = $dom->find('[src="src0"]');
        self::assertCount(1, $es);
        self::assertEquals('src0', $es[0]->src);
        self::assertEmpty($es[0]->innertext);
        self::assertEquals('<img class="class0" id="id0" src="src0">', $es[0]->outertext);

        $es = $dom->find('*#id1');
        self::assertCount(1, $es);
        self::assertEquals('src1', $es[0]->src);
        self::assertEmpty($es[0]->innertext);
        self::assertEquals('<img class="class1" id="id1" src="src1">', $es[0]->outertext);

        $es = $dom->find('*.class0');
        self::assertCount(1, $es);
        self::assertEquals('src0', $es[0]->src);
        self::assertEmpty($es[0]->innertext);
        self::assertEquals('<img class="class0" id="id0" src="src0">', $es[0]->outertext);
    }

    public function testSomething(): void
    {
        $dom = new SimpleHtmlDom();
        $str = '<b>text1</b><b>text2</b>';
        $dom->load($str);
        $es = $dom->find('text');
        self::assertCount(2, $es);
        self::assertEquals('text1', $es[0]->innertext);
        self::assertEquals('text1', $es[0]->outertext);
        self::assertEquals('text1', $es[0]->plaintext);
        self::assertEquals('text2', $es[1]->innertext);
        self::assertEquals('text2', $es[1]->outertext);
        self::assertEquals('text2', $es[1]->plaintext);

        $str = '<b>text1</b><b>text2</b>';
        $dom->load($str);
        $es = $dom->find('b text');
        self::assertCount(2, $es);
        self::assertEquals('text1', $es[0]->innertext);
        self::assertEquals('text1', $es[0]->outertext);
        self::assertEquals('text1', $es[0]->plaintext);
        self::assertEquals('text2', $es[1]->innertext);
        self::assertEquals('text2', $es[1]->outertext);
        self::assertEquals('text2', $es[1]->plaintext);
    }

    public function testXmlNamespace(): void
    {
        $dom = new SimpleHtmlDom();
        $str = '<bw:bizy id="date">text</bw:bizy>';
        $dom->load($str);
        $es = $dom->find('bw:bizy');
        self::assertCount(1, $es);
        self::assertEquals('date', $es[0]->id);
    }

    public function testUserDefinedTagName(): void
    {
        $dom = new SimpleHtmlDom();
        $str = '<div_test id="1">text</div_test>';
        $dom->load($str);
        $es = $dom->find('div_test');
        self::assertCount(1, $es);
        self::assertEquals('1', $es[0]->id);

        $str = '<div-test id="1">text</div-test>';
        $dom->load($str);
        $es = $dom->find('div-test');
        self::assertCount(1, $es);
        self::assertEquals('1', $es[0]->id);

        $str = '<div::test id="1">text</div::test>';
        $dom->load($str);
        $es = $dom->find('div::test');
        self::assertCount(1, $es);
        self::assertEquals('1', $es[0]->id);
    }
}