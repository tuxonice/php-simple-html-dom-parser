<?php

namespace Unit;

use PhpSimple\SimpleHtmlDom;
use PHPUnit\Framework\TestCase;

class DomTestCaseTest extends TestCase
{

    public function testDomTree(): void
    {
        $html = new SimpleHtmlDom();

        $html->load('');
        $e = $html->root;
        self::assertNull($e->first_child());
        self::assertNull($e->last_child());
        self::assertNull($e->next_sibling());
        self::assertNull($e->prev_sibling());
    }

    public function testGetDivElement(): void
    {
        $html = new SimpleHtmlDom();

        $str = '<div id="div1"></div>';
        $html->load($str);

        $e = $html->root;
        self::assertEquals('div1', $e->first_child()->id);
        self::assertEquals('div1', $e->last_child()->id);
        self::assertNull($e->next_sibling());
        self::assertNull($e->prev_sibling());
        self::assertEmpty($e->plaintext);
        self::assertEquals('<div id="div1"></div>', $e->innertext);
        self::assertEquals($str, $e->outertext);
    }

    public function testGetChildElements(): void
    {
        $html = new SimpleHtmlDom();

        $str = '<div id="div1"><div id="div10"></div><div id="div11"></div><div id="div12"></div></div>';
        $html->load($str);
        self::assertEquals($str, $html);

        $e = $html->find('div#div1', 0);
        self::assertTrue(isset($e->id));
        self::assertFalse(isset($e->_not_exist));
        self::assertEquals('div10', $e->first_child()->id);
        self::assertEquals('div12', $e->last_child()->id);
        self::assertNull($e->next_sibling());
        self::assertNull($e->prev_sibling());
    }

    public function testGetMultilevelElements(): void
    {
        $html = new SimpleHtmlDom();

        $str = '<div id="div0">';
        $str .= '<div id="div00"></div>';
        $str .= '</div>';
        $str .= '<div id="div1">';
        $str .= '<div id="div10"></div>';
        $str .= '<div id="div11"></div>';
        $str .= '<div id="div12"></div>';
        $str .= '</div>';
        $str .= '<div id="div2"></div>';

        $html->load($str);
        self::assertEquals($str, (string)$html);

        $e = $html->find('div#div1', 0);
        self::assertEquals('div10', $e->first_child()->id);
        self::assertEquals('div12', $e->last_child()->id);
        self::assertEquals('div2', $e->next_sibling()->id);
        self::assertEquals('div0', $e->prev_sibling()->id);

        $e = $html->find('div#div2', 0);
        self::assertNull($e->first_child());
        self::assertNull($e->last_child());

        $e = $html->find('div#div0 div#div00', 0);
        self::assertNull($e->first_child());
        self::assertNull($e->last_child());
        self::assertNull($e->next_sibling());
        self::assertNull($e->prev_sibling());
    }

    public function testDomTreeNavigation(): void
    {
        $html = new SimpleHtmlDom();

        $str = '<div id="div0"><div id="div00"></div></div>';
        $str .= '<div id="div1">';
        $str .= '<div id="div10"></div><div id="div11"><div id="div110"></div><div id="div111">';
        $str .= '<div id="div1110"></div><div id="div1111"></div><div id="div1112"></div>';
        $str .= '</div><div id="div112"></div></div><div id="div12"></div></div><div id="div2"></div>';
        $html->load($str);

        self::assertEquals($str, (string)$html);
        self::assertEquals('div1', $html->find("#div1", 0)->id);
        self::assertEquals('div10', $html->find("#div1", 0)->children(0)->id);
        self::assertEquals('div111', $html->find("#div1", 0)->children(1)->children(1)->id);
        self::assertEquals('div1112', $html->find("#div1", 0)->children(1)->children(1)->children(2)->id);
    }

    public function testNoValueAttribute(): void
    {
        $html = new SimpleHtmlDom();

        $str = '<form name="form1" method="post" action="">';
        $str .= '<input type="checkbox" name="checkbox0" checked value="checkbox0">aaa<br>';
        $str .= '<input type="checkbox" name="checkbox1" value="checkbox1">bbb<br>';
        $str .= '<input type="checkbox" name="checkbox2" value="checkbox2" checked>ccc<br>';
        $str .= '</form>';

        $html->load($str);
        self::assertEquals($str, (string)$html);

        $counter = 0;
        foreach($html->find('input[type=checkbox]') as $checkbox) {
            if (isset($checkbox->checked)) {
                self::assertEquals("checkbox$counter", $checkbox->value);
                $counter += 2;
            }
        }

        $counter = 0;
        foreach($html->find('input[type=checkbox]') as $checkbox) {
            if ($checkbox->checked) {
                self::assertEquals("checkbox$counter", $checkbox->value);
                $counter += 2;
            }
        }

        $es = $html->find('input[type=checkbox]');
        $es[1]->checked = true;
        self::assertEquals('<input type="checkbox" name="checkbox1" value="checkbox1" checked>', $es[1]->outertext);
        $es[0]->checked = false;
        self::assertEquals('<input type="checkbox" name="checkbox0" value="checkbox0">', $es[0]);
        $es[0]->checked = true;
        self::assertEquals('<input type="checkbox" name="checkbox0" checked value="checkbox0">', $es[0]->outertext);
    }

    public function testRemoveAttribute(): void
    {
        $html = new SimpleHtmlDom();
        $str = '<input type="checkbox" name="checkbox0"><input type = "checkbox" name = \'checkbox1\' value = "checkbox1">';

        $html->load($str);
        self::assertEquals($str, (string)$html);
        $e = $html->find('[name=checkbox0]', 0);
        $e->name = null;
        self::assertEquals('<input type="checkbox">', $e);
        $e->type = null;
        self::assertEquals('<input>', $e);

        $html->load($str);
        $e = $html->find('[name=checkbox1]', 0);
        $e->value = null;
        self::assertEquals("<input type = \"checkbox\" name = 'checkbox1'>", $e);
        $e->type = null;
        self::assertEquals("<input name = 'checkbox1'>", $e);
        $e->name = null;
        self::assertEquals('<input>', $e);
    }

    public function testRemoveNoValueAttribute(): void
    {
        $html = new SimpleHtmlDom();

        $str = '<input type="checkbox" checked name=\'checkbox0\'><input type="checkbox" name=\'checkbox1\' checked>';
        $html->load($str);
        self::assertEquals($str, (string)$html);
        $e = $html->find('[name=checkbox1]', 0);
        $e->type = NULL;
        self::assertEquals("<input name='checkbox1' checked>", $e);
        $e->name = null;
        self::assertEquals("<input checked>", $e);
        $e->checked = NULL;
        self::assertEquals("<input>", $e);

        $html->load($str);
        $e = $html->find('[name=checkbox0]', 0);
        $e->type = NULL;
        self::assertEquals("<input checked name='checkbox0'>", $e);
        $e->name = NULL;
        self::assertEquals('<input checked>', $e);
        $e->checked = NULL;
        self::assertEquals('<input>', $e);

        $html->load($str);

        $e = $html->find('[name=checkbox0]', 0);
        $e->checked = NULL;
        self::assertEquals("<input type=\"checkbox\" name='checkbox0'>", $e);
        $e->name = NULL;
        self::assertEquals('<input type="checkbox">', $e);
        $e->type = NULL;
        self::assertEquals("<input>", $e);
    }

    public function testExtractText(): void
    {
        $html = new SimpleHtmlDom();

        $str = '<b>okok</b>';
        $html->load($str);
        self::assertEquals($str, (string)$html);
        self::assertEquals('okok', $html->plaintext);

        $str = '<div><b>okok</b></div>';
        $html->load($str);
        self::assertEquals($str, (string)$html);
        self::assertEquals('okok', $html->plaintext);

        $str = '<div><b>okok</b>';
        $html->load($str);
        self::assertEquals($str, (string)$html);
        self::assertEquals('okok', $html->plaintext);

        $str = '<b>okok</b></div>';
        $html->load($str);
        self::assertEquals($str, (string)$html);
        self::assertEquals('okok</div>', $html->plaintext);
    }

    public function testOldFashionCamelNamingConventions(): void
    {
        $html = new SimpleHtmlDom();
        $str = '<input type="checkbox" id="checkbox" name="checkbox" value="checkbox" checked>';
        $str .= '<input type="checkbox" id="checkbox1" name="checkbox1" value="checkbox1">';
        $str .= '<input type="checkbox" id="checkbox2" name="checkbox2" value="checkbox2" checked>';

        $html->load($str);
        self::assertEquals($str, (string)$html);

        self::assertTrue($html->getElementByTagName('input')->hasAttribute('checked'));
        self::assertFalse($html->getElementsByTagName('input', 1)->hasAttribute('checked'));
        self::assertFalse($html->getElementsByTagName('input', 1)->hasAttribute('not_exist'));

        self::assertEquals($html->getElementByTagName('input')->getAttribute('value'), $html->find('input', 0)->value);
        self::assertEquals($html->getElementsByTagName('input', 1)->getAttribute('value'), $html->find('input', 1)->value);

        self::assertEquals($html->getElementById('checkbox1')->getAttribute('value'), $html->find('#checkbox1', 0)->value);
        self::assertEquals($html->getElementsById('checkbox2', 0)->getAttribute('value'), $html->find('#checkbox2', 0)->value);

        $e = $html->find('[name=checkbox]', 0);
        self::assertEquals('checkbox', $e->getAttribute('value'));
        self::assertEquals(1, $e->getAttribute('checked'));
        self::assertEquals('', $e->getAttribute('not_exist'));

        $e->setAttribute('value', 'okok');
        self::assertEquals('<input type="checkbox" id="checkbox" name="checkbox" value="okok" checked>', $e);

        $e->setAttribute('checked', false);
        self::assertEquals('<input type="checkbox" id="checkbox" name="checkbox" value="okok">', $e);

        $e->setAttribute('checked', true);
        self::assertEquals('<input type="checkbox" id="checkbox" name="checkbox" value="okok" checked>', $e);

        $e->removeAttribute('value');
        self::assertEquals('<input type="checkbox" id="checkbox" name="checkbox" checked>', $e);

        $e->removeAttribute('checked');
        self::assertEquals('<input type="checkbox" id="checkbox" name="checkbox">', $e);
    }

    public function testSomething(): void
    {
        $html = new SimpleHtmlDom();
        $str = '<div id="div1">';
        $str .= '<div id="div10"></div>';
        $str .= '<div id="div11"></div>';
        $str .= '<div id="div12"></div>';
        $str .= '</div>';

        $html->load($str);
        self::assertEquals($str, (string)$html);

        $e = $html->find('div#div1', 0);
        self::assertEquals('div10', $e->firstChild()->getAttribute('id'));
        self::assertEquals('div12', $e->lastChild()->getAttribute('id'));
        self::assertNull($e->nextSibling());
        self::assertNull($e->previousSibling());
    }

    public function testSomething2(): void
    {
        $html = new SimpleHtmlDom();
        $str = '<div id="div0">';
        $str .= '<div id="div00"></div>';
        $str .= '</div>';
        $str .= '<div id="div1">';
        $str .= '<div id="div10"></div>';
        $str .= '<div id="div11">';
        $str .= '<div id="div110"></div>';
        $str .= '<div id="div111">';
        $str .= '<div id="div1110"></div>';
        $str .= '<div id="div1111"></div>';
        $str .= '<div id="div1112"></div>';
        $str .= '</div>';
        $str .= '<div id="div112"></div>';
        $str .= '</div>';
        $str .= '<div id="div12"></div>';
        $str .= '</div>';
        $str .= '<div id="div2"></div>';

        $html->load($str);
        self::assertEquals($str, (string)$html);

        self::assertTrue($html->getElementById("div1")->hasAttribute('id'));
        self::assertFalse($html->getElementById("div1")->hasAttribute('not_exist'));

        self::assertEquals('div1', $html->getElementById("div1")->getAttribute('id'));
        self::assertEquals('div10', $html->getElementById("div1")->childNodes(0)->getAttribute('id'));
        self::assertEquals('div111', $html->getElementById("div1")->childNodes(1)->childNodes(1)->getAttribute('id'));
        self::assertEquals('div1112', $html->getElementById("div1")->childNodes(1)->childNodes(1)->childNodes(2)->getAttribute('id'));

        self::assertEquals('div11', $html->getElementsById("div1", 0)->childNodes(1)->id);
        self::assertEquals('div111', $html->getElementsById("div1", 0)->childNodes(1)->childNodes(1)->getAttribute('id'));
        self::assertEquals('div1111', $html->getElementsById("div1", 0)->childNodes(1)->childNodes(1)->childNodes(1)->getAttribute('id'));
    }

    public function testSomething3(): void
    {
        $html = new SimpleHtmlDom();
        $str = '<ul class="menublock">';
        $str .= '</li>';
        $str .= '<ul>';
        $str .= '<li>';
        $str .= '<a href="http://www.cyberciti.biz/tips/pollsarchive">Polls Archive</a>';
        $str .= '</li>';
        $str .= '</ul>';
        $str .= '</li>';
        $str .= '</ul>';

        $html->load($str);

        $ul = $html->find('ul', 0);
        self::assertEquals('ul', $ul->first_child()->tag);
    }

    public function testSomething4(): void
    {
        $html = new SimpleHtmlDom();
        $str = '<ul>';
        $str .= '<li>Item 1 ';
        $str .= '<ul>';
        $str .= '<li>Sub Item 1 </li>';
        $str .= '<li>Sub Item 2 </li>';
        $str .= '</ul>';
        $str .= '</li>';
        $str .= '<li>Item 2 </li>';
        $str .= '</ul>';

        $html->load($str);
        self::assertEquals($str, (string)$html);

        $ul = $html->find('ul', 0);
        self::assertEquals('li', $ul->first_child()->tag);
        self::assertEquals('li', $ul->first_child()->next_sibling()->tag);
    }
}