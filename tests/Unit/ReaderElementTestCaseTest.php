<?php

namespace Unit;

use PhpSimple\SimpleHtmlDom;
use PHPUnit\Framework\TestCase;

class ReaderElementTestCaseTest extends TestCase
{

    public function testAttribute(): void
    {
        $dom = new SimpleHtmlDom();
        $str = '<div onclick="bar(\'aa\')">foo</div>';
        $dom->load($str);
        self::assertEquals($str, $dom->find('div', 0));

        $str = '<div onclick=\'bar("aa")\'>foo</div>';
        $dom->load($str);
        self::assertEquals($str, $dom->find('div', 0));
    }


    public function testInnertext(): void
    {
        $dom = new SimpleHtmlDom();
        $str = '<html><head></head><body><br><span>foo</span></body></html>';
        $dom->load($str);
        self::assertEquals($str, (string)$dom);

        $str = '<html><head></head><body><br><span>bar</span></body></html>';
        $dom->find('span', 0)->innertext = 'bar';
        self::assertEquals($str, (string)$dom);

        $str = '<html><head>ok</head><body><br><span>bar</span></body></html>';
        $dom->find('head', 0)->innertext = 'ok';
        self::assertEquals($str, (string)$dom);
    }

    public function testOutertext(): void
    {
        $dom = new SimpleHtmlDom();
        $str = <<<HTML
<table>
<tr><th>Head1</th><th>Head2</th><th>Head3</th></tr>
<tr><td>1</td><td>2</td><td>3</td></tr>
</table>
HTML;
        $dom->load($str);
        self::assertEquals('<tr><th>Head1</th><th>Head2</th><th>Head3</th></tr>', $dom->find('tr', 0)->outertext);
        self::assertEquals('<tr><td>1</td><td>2</td><td>3</td></tr>', $dom->find('tr', 1)->outertext);

        $str = '<table><tr><th>Head1</th><th>Head2</th><th>Head3</th></tr><tr><td>1</td><td>2</td><td>3</td></tr></table>';
        $dom->load($str);
        self::assertEquals('<tr><th>Head1</th><th>Head2</th><th>Head3</th></tr>', $dom->find('tr', 0)->outertext);
        self::assertEquals('<tr><td>1</td><td>2</td><td>3</td></tr>', $dom->find('tr', 1)->outertext);

        $str = '<ul><li><b>li11</b></li><li><b>li12</b></li></ul><ul><li><b>li21</b></li><li><b>li22</b></li></ul>';
        $dom->load($str);
        self::assertEquals('<ul><li><b>li11</b></li><li><b>li12</b></li></ul>', $dom->find('ul', 0)->outertext);
        self::assertEquals('<ul><li><b>li21</b></li><li><b>li22</b></li></ul>', $dom->find('ul', 1)->outertext);

        $str = <<<HTML
<table>
<tr><th>Head1</th><th>Head2</th><th>Head3</th></tr>
<tr><td>1</td><td>2</td><td>3</td></tr>
</table>
HTML;
        $dom->load($str);
        self::assertEquals('<tr><th>Head1</th><th>Head2</th><th>Head3</th></tr>', $dom->find('tr', 0)->outertext);
        self::assertEquals('<tr><td>1</td><td>2</td><td>3</td></tr>', $dom->find('tr', 1)->outertext);
    }

    public function testReplacement(): void
    {
        $dom = new SimpleHtmlDom();
        $str = '<div class="class1" id="id2"><div class="class2">ok</div></div>';
        $dom->load($str);
        $es = $dom->find('div');
        self::assertCount(2, $es);
        self::assertEquals('<div class="class2">ok</div>', $es[0]->innertext);
        self::assertEquals('<div class="class1" id="id2"><div class="class2">ok</div></div>', $es[0]->outertext);

        // test isset
        $es[0]->class = 'class_test';
        self::assertTrue(isset($es[0]->class));
        self::assertFalse(isset($es[0]->okok));

        // test replacement
        $es[0]->class = 'class_test';
        self::assertEquals('<div class="class_test" id="id2"><div class="class2">ok</div></div>', $es[0]->outertext);

        $str = '<select name="something"><options>blah</options><options>blah2</options></select>';
        $dom->load($str);
        $e = $dom->find('select[name=something]', 0);
        $e->innertext = '';
        self::assertEquals('<select name="something"></select>', $e->outertext);
    }

    public function testNestedReplacement(): void
    {
        $dom = new SimpleHtmlDom();
        $str = '<div class="class0" id="id0"><div class="class1">ok</div></div>';
        $dom->load($str);
        $es = $dom->find('div');
        self::assertCount(2, $es);
        self::assertEquals('<div class="class1">ok</div>', $es[0]->innertext);
        self::assertEquals('<div class="class0" id="id0"><div class="class1">ok</div></div>', $es[0]->outertext);
        self::assertEquals('ok', $es[1]->innertext);
        self::assertEquals('<div class="class1">ok</div>', $es[1]->outertext);

        // test replacement
        $es[1]->innertext = 'okok';
        self::assertEquals('<div class="class1">okok</div>', $es[1]->outertext);
        self::assertEquals('<div class="class0" id="id0"><div class="class1">okok</div></div>', $es[0]->outertext);

        $es[1]->class = 'class_test';
        self::assertEquals('<div class="class_test">okok</div>', $es[1]->outertext);
        self::assertEquals('<div class="class0" id="id0"><div class="class_test">okok</div></div>', $es[0]->outertext);

        $es[0]->class = 'class_test';
        self::assertEquals('<div class="class_test" id="id0"><div class="class_test">okok</div></div>', $es[0]->outertext);

        $es[0]->innertext = 'okokok';
        self::assertEquals('<div class="class_test" id="id0">okokok</div>', $es[0]->outertext);
    }

    public function testParagraphTag(): void
    {
        $dom = new SimpleHtmlDom();
        $str = <<<HTML
<div class="class0"><p>ok0<a href="#">link0</a></p><div class="class1"><p>ok1<a href="#">link1</a></p></div><div class="class2"></div><p>ok2<a href="#">link2</a></p></div>
HTML;
        $dom->load($str);
        $es  = $dom->find('p');
        self::assertEquals('ok0<a href="#">link0</a>', $es[0]->innertext);
        self::assertEquals('ok1<a href="#">link1</a>', $es[1]->innertext);
        self::assertEquals('ok2<a href="#">link2</a>', $es[2]->innertext);
        self::assertEquals('ok0link0', $dom->find('p', 0)->plaintext);
        self::assertEquals('ok1link1', $dom->find('p', 1)->plaintext);
        self::assertEquals('ok2link2', $dom->find('p', 2)->plaintext);

        $count = 0;
        foreach($dom->find('p') as $p) {
            $a = $p->find('a');
            self::assertEquals('link'.$count, $a[0]->innertext);
            ++$count;
        }

        $es = $dom->find('p a');
        self::assertEquals('link0', $es[0]->innertext);
        self::assertEquals('link1', $es[1]->innertext);
        self::assertEquals('link2', $es[2]->innertext);
        self::assertEquals('link0', $dom->find('p a', 0)->plaintext);
        self::assertEquals('link1', $dom->find('p a', 1)->plaintext);
        self::assertEquals('link2', $dom->find('p a', 2)->plaintext);
    }

    public function testEmbededTag(): void
    {
        $dom = new SimpleHtmlDom();
        $str = '<EMBED SRC="../graphics/sounds/1812over.mid" HEIGHT="60" WIDTH="144">';
        $dom->load($str);
        $e = $dom->find('embed', 0);
        self::assertEquals('../graphics/sounds/1812over.mid', $e->src);
        self::assertEquals('60', $e->height);
        self::assertEquals('144', $e->width);
        self::assertEquals(strtolower($str), (string)$e);

    }

    public function testPreTag(): void
    {
        $dom = new SimpleHtmlDom();
        $str = <<<HTML
<div class="class0" id="id0" >
    <pre>
        <input type=submit name="btnG" value="go" onclick='goto("url0")'>
    </pre>
</div>
HTML;
        $dom->load($str);
        self::assertCount(1, $dom->find('input'));

    }

    public function testCodeTag(): void
    {
        $this->markTestSkipped();
//        $dom = new SimpleHtmlDom();
//        $str = <<<HTML
//<div class="class0" id="id0" >
//    <CODE>
//        <input type=submit name="btnG" value="go" onclick='goto("url0")'>
//    </CODE>
//</div>
//HTML;
//        $dom->load($str);
//        self::assertCount(1, $dom->find('code'));
//        self::assertCount(1, $dom->find('input'));
    }
}