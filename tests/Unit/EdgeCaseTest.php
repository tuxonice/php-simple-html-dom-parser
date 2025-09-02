<?php

namespace Unit;

use Tlab\HtmlDomParser\HtmlDomParser;
use Tlab\HtmlDomParser\SimpleHtmlDom;
use PHPUnit\Framework\TestCase;

class EdgeCaseTest extends TestCase
{
    public function testMalformedHtml(): void
    {
        $malformedHtml = '<div><p>Unclosed paragraph<div>Nested without closing</span>';
        $dom = HtmlDomParser::strGetHtml($malformedHtml);
        
        $this->assertNotNull($dom);
        $divs = $dom->find('div');
        $this->assertGreaterThan(0, count($divs));
    }
    
    public function testSelfClosingTagVariations(): void
    {
        $variations = [
            '<br>',
            '<br/>',
            '<br />',
            '<img src="test.jpg">',
            '<img src="test.jpg"/>',
            '<img src="test.jpg" />',
            '<input type="text">',
            '<input type="text"/>',
            '<input type="text" />'
        ];
        
        foreach ($variations as $html) {
            $dom = HtmlDomParser::strGetHtml($html);
            $this->assertNotNull($dom, "Failed to parse: {$html}");
        }
    }
    
    public function testCommentsAndCdata(): void
    {
        $html = '
        <div>
            <!-- This is a comment -->
            <![CDATA[This is CDATA content]]>
            <p>Regular content</p>
        </div>';
        
        $dom = HtmlDomParser::strGetHtml($html);
        
        $comments = $dom->find('comment');
        $this->assertCount(1, $comments);
        
        $paragraphs = $dom->find('p');
        $this->assertCount(1, $paragraphs);
        $this->assertEquals('Regular content', $paragraphs[0]->plaintext);
    }
    
    public function testDoctypeHandling(): void
    {
        $htmlWithDoctype = '<!DOCTYPE html><html><body><div>Content</div></body></html>';
        $dom = HtmlDomParser::strGetHtml($htmlWithDoctype);
        
        $divs = $dom->find('div');
        $this->assertCount(1, $divs);
        $this->assertEquals('Content', $divs[0]->plaintext);
    }
    
    public function testWhitespaceHandling(): void
    {
        $htmlWithWhitespace = "
        <div>
            <p>   Paragraph with spaces   </p>
            <span>\t\nTab and newline\t\n</span>
        </div>";
        
        $dom = HtmlDomParser::strGetHtml($htmlWithWhitespace);
        
        $this->assertNotNull($dom);
        $this->assertStringContainsString('Paragraph', $dom->plaintext);
        $span = $dom->find('span', 0);
        $this->assertNotNull($span);
        $this->assertStringContainsString('Tab and newline', $span->plaintext);
    }
    
    public function testAttributeEdgeCases(): void
    {
        $html = '
        <div 
            id="test-id"
            class="class1 class2"
            data-value=""
            checked
            disabled="disabled"
            style="color: red; background: blue;"
            data-test="test"
        >Content</div>';
        
        $dom = HtmlDomParser::strGetHtml($html);
        $div = $dom->find('div', 0);
        
        $this->assertEquals('test-id', $div->id);
        $this->assertEquals('class1 class2', $div->class);
        $this->assertEquals('', $div->getAttribute('data-value'));
        $this->assertTrue($div->hasAttribute('checked'));
        $this->assertEquals('disabled', $div->disabled);
        $this->assertStringContainsString('color: red', $div->style);
        $this->assertEquals('test', $div->getAttribute('data-test'));
    }
    
    public function testQuotedAttributeVariations(): void
    {
        $variations = [
            '<div id="double-quoted">',
            "<div id='single-quoted'>",
            '<div id=unquoted>',
            '<div id="mixed\'quotes">',
            '<div id=\'mixed"quotes\'>'
        ];
        
        foreach ($variations as $html) {
            $dom = HtmlDomParser::strGetHtml($html . '</div>');
            $div = $dom->find('div', 0);
            $this->assertNotNull($div, "Failed to parse: {$html}");
            $this->assertNotEmpty($div->id, "No ID found in: {$html}");
        }
    }
    
    public function testNestedQuotesInAttributes(): void
    {
        $html = '<div title="He said &quot;Hello&quot;" data-json=\'{"key": "value"}\' data-test="test">Content</div>';
        $dom = HtmlDomParser::strGetHtml($html);
        
        $div = $dom->find('div', 0);
        $this->assertStringContainsString('Hello', $div->title);
        $this->assertEquals('test', $div->getAttribute('data-test'));
    }
    
    public function testEmptyAndNullValues(): void
    {
        $html = '<div id="" class=" " data-empty="" data-space=" ">Content</div>';
        $dom = HtmlDomParser::strGetHtml($html);
        
        $div = $dom->find('div', 0);
        $this->assertEquals('Content', $div->plaintext);
        $this->assertEquals('', $div->id);
        $this->assertEquals('', $div->getAttribute('data-empty'));
    }
    
    public function testSpecialHtmlEntities(): void
    {
        $html = '<div>&amp; &lt; &gt; &quot; &#39; &nbsp; &copy;</div>';
        $dom = HtmlDomParser::strGetHtml($html);
        
        $div = $dom->find('div', 0);
        $this->assertNotNull($div);
        $this->assertStringContainsString('&amp;', $div->plaintext);
    }
}
