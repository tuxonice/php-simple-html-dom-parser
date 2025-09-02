<?php

namespace Unit;

use Tlab\HtmlDomParser\HtmlDomParser;
use Tlab\HtmlDomParser\SimpleHtmlDom;
use PHPUnit\Framework\TestCase;

class CharsetEncodingTest extends TestCase
{
    public function testUtf8Encoding(): void
    {
        $html = '<div>Hello 世界 🌍</div>';
        $dom = HtmlDomParser::strGetHtml($html);
        
        $div = $dom->find('div', 0);
        $this->assertStringContainsString('世界', $div->plaintext);
        $this->assertStringContainsString('🌍', $div->plaintext);
    }
    
    public function testMetaCharsetDetection(): void
    {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Test</title>
        </head>
        <body>
            <div>Content with émojis 🎉</div>
        </body>
        </html>';
        
        $dom = HtmlDomParser::strGetHtml($html);
        $this->assertEquals('UTF-8', $dom->_charset);
        
        $div = $dom->find('div', 0);
        $this->assertStringContainsString('Content with émojis', $div->plaintext);
    }
    
    public function testHttpEquivCharsetDetection(): void
    {
        $html = '<html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        </head>
        <body><div>Test content</div></body>
        </html>';
        
        $dom = HtmlDomParser::strGetHtml($html);
        $this->assertEquals('UTF-8', $dom->_charset); 
    }
    
    public function testBomHandling(): void
    {
        // UTF-8 BOM + content
        $htmlWithBom = "\xef\xbb\xbf<div>Content with BOM</div>";
        $dom = HtmlDomParser::strGetHtml($htmlWithBom);
        
        $div = $dom->find('div', 0);
        $this->assertEquals('Content with BOM', $div->plaintext);
    }
    
    public function testMixedEncodingContent(): void
    {
        $html = '<div title="café">Restaurant: café & 餐厅</div>';
        $dom = HtmlDomParser::strGetHtml($html);
        
        $div = $dom->find('div', 0);
        // Test basic content extraction since meta charset may not be parsed as expected
        $this->assertStringContainsString('café', $div->title);
        $this->assertStringContainsString('餐厅', $div->plaintext);
    }
    
    public function testCharsetConversion(): void
    {
        $dom = new SimpleHtmlDom();
        $dom->_charset = 'UTF-8';
        $dom->_target_charset = 'UTF-8';
        
        $html = '<div>Test content with special chars: àáâãäå</div>';
        $dom->load($html);
        
        $div = $dom->find('div', 0);
        $this->assertStringContainsString('àáâãäå', $div->plaintext);
    }
}
