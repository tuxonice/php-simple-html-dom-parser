<?php

namespace Unit;

use PhpSimple\SimpleHtmlDom;
use PHPUnit\Framework\TestCase;

class InvalidTestCaseTest extends TestCase
{

    public function testSelfClosingTags(): void
    {
        $dom = new SimpleHtmlDom();
        $str = '<hr>';
        $dom->load($str);
        $e = $dom->find('hr', 0);
        $e->id = 'foo';
        self::assertEquals('<hr id="foo">', $e->outertext);

        $str = '<hr/>';
        $dom->load($str);
        $e = $dom->find('hr', 0);
        $e->id= 'foo';
        self::assertEquals('<hr id="foo"/>', $e->outertext);

        $str = '<hr />';
        $dom->load($str);
        $e = $dom->find('hr', 0);
        $e->id= 'foo';
        self::assertEquals('<hr id="foo" />', $e->outertext);

        $str = '<hr>';
        $dom->load($str);
        $e = $dom->find('hr', 0);
        $e->id= 'foo';
        $e->class = 'bar';
        self::assertEquals('<hr id="foo" class="bar">', $e->outertext);

        $str = '<hr/>';
        $dom->load($str);
        $e = $dom->find('hr', 0);
        $e->id= 'foo';
        $e->class = 'bar';
        self::assertEquals('<hr id="foo" class="bar"/>', $e->outertext);

        $str = '<hr />';
        $dom->load($str);
        $e = $dom->find('hr', 0);
        $e->id= 'foo';
        $e->class = 'bar';
        self::assertEquals('<hr id="foo" class="bar" />', $e->outertext);

        $str = '<hr id="foo" kk=ll>';
        $dom->load($str);
        $e = $dom->find('hr', 0);
        $e->class = 'bar';
        self::assertEquals('<hr id="foo" kk=ll class="bar">', $e->outertext);

        $str = '<hr id="foo" kk="ll"/>';
        $dom->load($str);
        $e = $dom->find('hr', 0);
        $e->class = 'bar';
        self::assertEquals('<hr id="foo" kk="ll" class="bar"/>', $e->outertext);

        $str = '<hr id="foo" kk=ll />';
        $dom->load($str);
        $e = $dom->find('hr', 0);
        $e->class = 'bar';
        self::assertEquals('<hr id="foo" kk=ll class="bar" />', $e->outertext);

        $str = '<div><nobr></div>';
        $dom->load($str);
        $e = $dom->find('nobr', 0);
        self::assertEquals('<nobr>', $e->outertext);
    }

    public function testOptionalClosingTags(): void
    {
        $dom = new SimpleHtmlDom();
        $str = '<body></b><.b></a></body>';

        $dom->load($str);
        self::assertEquals($str, $dom->find('body', 0)->outertext);

        $str = '<html><body><a>foo</a><a>foo2</a>';
        $dom->load($str);
        self::assertEquals($str, (string)$dom);
        self::assertEquals('foo2', $dom->find('html body a', 1)->innertext);

        $str = '';
        $dom->load($str);
        self::assertEquals($str, (string)$dom);
        self::assertNull($dom->find('html a', 1));

        $str = '<body><div></body>';
        $dom->load($str);
        self::assertEquals($str, (string)$dom);
        self::assertEquals($str, $dom->find('body', 0)->outertext);


        $str = '<body><div> </a> </div></body>';
        $dom->load($str);
        self::assertEquals($str, $dom->find('body', 0)->outertext);

        $str = '<table> <tr>  <td><b>aa</b> <tr>   <td><b>bb</b></table>';
        $dom->load($str);
        self::assertEquals($str, (string)$dom);

        $str = <<<HTML
<table>
<tr><td>1<td>2<td>3
</table>
HTML;
        $dom->load($str);
        self::assertEquals(3, count($dom->find('td')));
        self::assertEquals('1', $dom->find('td', 0)->innertext);
        self::assertEquals('<td>1', $dom->find('td', 0)->outertext);
        self::assertEquals('2', $dom->find('td', 1)->innertext);
        self::assertEquals('<td>2', $dom->find('td', 1)->outertext);
        self::assertEquals("3 ", $dom->find('td', 2)->innertext);
        self::assertEquals("<td>3 ", $dom->find('td', 2)->outertext);

        $str = <<<HTML
<table>
<tr>
    <td><b>1</b></td>
    <td><b>2</b></td>
    <td><b>3</b></td>
</table>
HTML;
        $dom->load($str);
        self::assertCount(3, $dom->find('tr td'));

        $str = <<<HTML
<table>
<tr><td><b>11</b></td><td><b>12</b></td><td><b>13</b></td>
<tr><td><b>21</b></td><td><b>32</b></td><td><b>43</b></td>
</table>
HTML;
        $dom->load($str);
        self::assertCount(2, $dom->find('tr'));
        self::assertCount(6, $dom->find('tr td'));
        self::assertEquals("<tr><td><b>21</b></td><td><b>32</b></td><td><b>43</b></td> ", $dom->find('tr', 1)->outertext);
        self::assertEquals("<td><b>21</b></td><td><b>32</b></td><td><b>43</b></td> ", $dom->find('tr', 1)->innertext);
        self::assertEquals("213243 ", $dom->find('tr', 1)->plaintext);

        $str = <<<HTML
<p>1
<p>2</p>
<p>3
HTML;
        $dom->load($str);
        self::assertCount(3, $dom->find('p'));
        self::assertEquals("1 ", $dom->find('p', 0)->innertext);
        self::assertEquals("<p>1 ", $dom->find('p', 0)->outertext);
        self::assertEquals("2", $dom->find('p', 1)->innertext);
        self::assertEquals("<p>2</p>", $dom->find('p', 1)->outertext);
        self::assertEquals("3", $dom->find('p', 2)->innertext);
        self::assertEquals("<p>3", $dom->find('p', 2)->outertext);

        $str = <<<HTML
<nobr>1
<nobr>2</nobr>
<nobr>3
HTML;
        $dom->load($str);
        self::assertCount(3, $dom->find('nobr'));
        self::assertEquals("1 ", $dom->find('nobr', 0)->innertext);
        self::assertEquals("<nobr>1 ", $dom->find('nobr', 0)->outertext);
        self::assertEquals("2", $dom->find('nobr', 1)->innertext);
        self::assertEquals("<nobr>2</nobr>", $dom->find('nobr', 1)->outertext);
        self::assertEquals("3", $dom->find('nobr', 2)->innertext);
        self::assertEquals("<nobr>3", $dom->find('nobr', 2)->outertext);

        $str = <<<HTML
<dl><dt>1<dd>2<dt>3<dd>4</dl>
HTML;
        $dom->load($str);
        self::assertCount(2, $dom->find('dt'));
        self::assertCount(2, $dom->find('dd'));
        self::assertEquals("1", $dom->find('dt', 0)->innertext);
        self::assertEquals("<dt>1", $dom->find('dt', 0)->outertext);
        self::assertEquals("3", $dom->find('dt', 1)->innertext);
        self::assertEquals("<dt>3", $dom->find('dt', 1)->outertext);
        self::assertEquals("2", $dom->find('dd', 0)->innertext);
        self::assertEquals("<dd>2", $dom->find('dd', 0)->outertext);
        self::assertEquals("4", $dom->find('dd', 1)->innertext);
        self::assertEquals("<dd>4", $dom->find('dd', 1)->outertext);

        $str = <<<HTML
<dl id="dl1"><dt>11<dd>12<dt>13<dd>14</dl>
<dl id="dl2"><dt>21<dd>22<dt>23<dd>24</dl>
HTML;
        $dom->load($str);
        self::assertCount(2, $dom->find('#dl1 dt'));
        self::assertCount(2, $dom->find('#dl2  dd'));
        self::assertEquals("<dt>11<dd>12<dt>13<dd>14", $dom->find('dl', 0)->innertext);
        self::assertEquals("<dt>21<dd>22<dt>23<dd>24", $dom->find('dl', 1)->innertext);

        $str = <<<HTML
<ul id="ul1"><li><b>1</b><li><b>2</b></ul>
<ul id="ul2"><li><b>3</b><li><b>4</b></ul>
HTML;
        $dom->load($str);
        self::assertCount(2, $dom->find('ul[id=ul1] li'));
    }

    public function testInvalid(): void
    {
        $dom = new SimpleHtmlDom();
        $str = <<<HTML
<div>
    <div class="class0" id="id0" >
    <img class="class0" id="id0" src="src0">
    </img>
    <img class="class0" id="id0" src="src0">
    </div>
</div>
HTML;
        $dom->load($str);
        self::assertCount(2, $dom->find('img'));
        self::assertCount(2,$dom->find('img'));

        $str = <<<HTML
<div>
    <div class="class0" id="id0" >
    <span></span>
    </span>
    <span></span>
    </div>
</div>
HTML;

        $dom->load($str);
        self::assertCount(2, $dom->find('span'));
        self::assertCount(2, $dom->find('div'));

        $str = <<<HTML
<div>
    <div class="class0" id="id0" >
    <span></span>
    <span>
    <span></span>
    </div>
</div>
HTML;
        $dom->load($str);
        self::assertCount(3, $dom->find('span'));
        self::assertCount(2, $dom->find('div'));

        $str = <<<HTML
<ul class="menublock">
    </li>
        <ul>
            <li>
                <a href="http://www.cyberciti.biz/tips/pollsarchive">Polls Archive</a>
            </li>
        </ul>
    </li>
</ul>
HTML;
        $dom->load($str);
        self::assertCount(2, $dom->find('ul'));
        self::assertCount(1,$dom->find('ul ul'));
        self::assertCount(1,$dom->find('li'));
        self::assertCount(1, $dom->find('a'));

        $str = <<<HTML
<td>
    <div>
        </span>
    </div>
</td>
HTML;
        $dom->load($str);
        self::assertCount(1, $dom->find('td'));
        self::assertCount(1, $dom->find('div'));
        self::assertCount(1, $dom->find('td div'));

        $str = <<<HTML
<td>
    <div>
        </b>
    </div>
</td>
HTML;
        $dom->load($str);
        self::assertCount(1,$dom->find('td'));
        self::assertCount(1, $dom->find('div'));
        self::assertCount(1, $dom->find('td div'));

        $str = <<<HTML
<td>
    <div></div>
    </div>
</td>
HTML;
        $dom->load($str);
        self::assertCount(1, $dom->find('td'));
        self::assertCount(1, $dom->find('div'));
        self::assertCount(1, $dom->find('td div'));

        $str = <<<HTML
<html>
    <body>
        <table>
            <tr>
                foo</span>
                <span>bar</span>
                </span>important
            </tr>
        </table>
    </bod>
</html>
HTML;
        $dom->load($str);
        self::assertCount(1, $dom->find('table span'));
        self::assertSame('bar', $dom->find('table span', 0)->innertext);

        $str = <<<HTML
<td>
    <div>
        <font>
            <b>foo</b>
    </div>
</td>
HTML;
        $dom->load($str);
        self::assertCount(1, $dom->find('td div font b'));

        $str = <<<HTML
<span style="okokok">
... then slow into 287 
    <i> 
        <b> 
            <font color="#0000CC">(hanover0...more volume between 202 & 53 
            <i> 
                <b> 
                    <font color="#0000CC">(parsippany)</font> 
                </b>
            </i>
            ...then sluggish in spots out to dover chester road 
            <i> 
                <b> 
                    <font color="#0000CC">(randolph)</font> 
                </b> 
            </i>..then traffic light delays out to route 46 
            <i> 
                <b> 
                    <font color="#0000CC">(roxbury)</font> 
                </b> 
            </i>/eb slow into 202 
            <i> 
                <b> 
                    <font color="#0000CC">(morris plains)</font> 
                </b> 
            </i> & again into 287 
            <i> 
                <b> 
                    <font color="#0000CC">(hanover)</font>
                </b> 
            </i> 
</span>. 
<td class="d N4 c">52</td> 
HTML;
        $dom->load($str);
        self::assertCount(0, $dom->find('span td'));
    }

    public function testInvalidOpenTagChar(): void
    {
        $dom = new SimpleHtmlDom();
        $str = <<<HTML
<td><b>test :</b>1 gram but <5 grams</td>
HTML;
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but <5 grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but <5 grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);

        $str = <<<HTML
<td><b>test :</b>1 gram but<5 grams</td>
HTML;
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but<5 grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but<5 grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);

        $str = <<<HTML
<td><b>test :</b>1 gram but< 5 grams</td>
HTML;
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but< 5 grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but< 5 grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);

        $str = <<<HTML
<td><b>test :</b>1 gram but < 5 grams</td>
HTML;
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but < 5 grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but < 5 grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);

        $str = <<<HTML
<td><b>test :</b>1 gram but 5< grams</td>
HTML;
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but 5< grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but 5< grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);

        $str = <<<HTML
<td><b>test :</b>1 gram but 5 < grams</td>
HTML;
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but 5 < grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but 5 < grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);

        $str = <<<HTML
<td><b>test :</b>1 gram but 5 <grams</td>
HTML;
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but 5 <grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but 5 <grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);

        $str = <<<HTML
<td><b>test :</b>1 gram but 5< grams</td>
HTML;
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but 5< grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but 5< grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);

        $str = <<<HTML
<td><b>test :</b>1 gram but5< grams</td>
HTML;
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but5< grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but5< grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);

        $str = <<<HTML
<td><b>test :</b>1 gram but 5 <grams</td>
HTML;
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but 5 <grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but 5 <grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);

        $str = <<<HTML
<td><b>test :</b>1 gram but 5<grams</td>
HTML;
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but 5<grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but 5<grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);

        $str = <<<HTML
<td><b>test :</b>1 gram but 5 <grams</td>
HTML;
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but 5 <grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but 5 <grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);
    }

    public function testInvalidCloseTagChar(): void
    {
        $dom = new SimpleHtmlDom();
        $str = '<td><b>test :</b>1 gram but >5 grams</td>';

        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but >5 grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but >5 grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);

        $str = '<td><b>test :</b>1 gram but>5 grams</td>';
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but>5 grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but>5 grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);

        $str = '<td><b>test :</b>1 gram but> 5 grams</td>';
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but> 5 grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but> 5 grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);

        $str = '<td><b>test :</b>1 gram but > 5 grams</td>';
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but > 5 grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but > 5 grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);

        $str = '<td><b>test :</b>1 gram but 5> grams</td>';
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but 5> grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but 5> grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);

        $str = '<td><b>test :</b>1 gram but 5 > grams</td>';
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but 5 > grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but 5 > grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);

        $str = '<td><b>test :</b>1 gram but 5 >grams</td>';
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but 5 >grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but 5 >grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);

        $str = '<td><b>test :</b>1 gram but 5> grams</td>';
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but 5> grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but 5> grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);

        $str = '<td><b>test :</b>1 gram but5> grams</td>';
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but5> grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but5> grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);

        $str = '<td><b>test :</b>1 gram but 5 >grams</td>';
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but 5 >grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but 5 >grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);

        $str = '<td><b>test :</b>1 gram but 5>grams</td>';
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but 5>grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but 5>grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);

        $str = '<td><b>test :</b>1 gram but 5 >grams</td>';
        $dom->load($str);
        self::assertSame('<b>test :</b>1 gram but 5 >grams', $dom->find('td', 0)->innertext);
        self::assertSame('test :1 gram but 5 >grams', $dom->find('td', 0)->plaintext);
        self::assertEquals($str, (string)$dom);
    }

}