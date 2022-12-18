<?php

namespace Unit;

use PhpSimple\SimpleHtmlDom;
use PHPUnit\Framework\TestCase;

class CallbackTestCaseTest extends TestCase
{

    public function testProblemOfLastElementNotFound(): void
    {
        $dom = new SimpleHtmlDom();

        $str = '<img src="src0"><p>foo</p><img src="src2">';

        $callback_1 = function($e) {
            if ($e->tag==='img') {
                $e->outertext = '';
            }
        };

        $dom->load($str);
        $dom->set_callback($callback_1);
        self::assertEquals('<p>foo</p>', $dom);
    }

    public function testInnertext(): void
    {
        $dom = new SimpleHtmlDom();

        $str = '<img src="src0"><p>foo</p><img src="src2">';
        $callback_2 = function($e) {
            if ($e->tag==='p')
                $e->innertext = 'bar';
        };

        $dom->load($str);
        $dom->set_callback($callback_2);
        self::assertEquals('<img src="src0"><p>bar</p><img src="src2">', $dom);
    }

    public function testAttributes(): void
    {
        $dom = new SimpleHtmlDom();
        $str = '<img src="src0"><p>foo</p><img src="src2">';

        $callback_3 = function($e) {
            if ($e->tag==='img')
                $e->src = 'foo';
        };

        $dom->load($str);
        $dom->set_callback($callback_3);
        self::assertEquals('<img src="foo"><p>foo</p><img src="foo">', $dom);

        $callback_4 = function($e) {
            if ($e->tag==='img')
                $e->id = 'foo';
        };

        $dom->set_callback($callback_4);
        self::assertEquals('<img src="foo" id="foo"><p>foo</p><img src="foo" id="foo">', $dom);

        // attributes test2
        //$dom = str_get_dom($str);
        $dom->load($str);
        $dom->remove_callback();
        $dom->find('img', 0)->id = "foo";
        self::assertEquals('<img src="src0" id="foo"><p>foo</p><img src="src2">', $dom);

        $callback_5 = function($e) {
            if ($e->src==='src0')
                unset($e->id);
        };

        $dom->set_callback($callback_5);
        self::assertEquals($str, $dom);
    }
}
