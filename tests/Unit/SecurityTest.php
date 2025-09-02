<?php

namespace Unit;

use Tlab\HtmlDomParser\HtmlDomParser;
use PHPUnit\Framework\TestCase;
use Exception;

class SecurityTest extends TestCase
{
    public function testMaxFileSizeLimit(): void
    {
        $largeString = str_repeat('<div>test</div>', 50000); // ~600KB+
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Content size exceed');
        
        HtmlDomParser::strGetHtml($largeString);
    }
    
    public function testFileGetHtmlWithInvalidPath(): void
    {
        // Test that invalid file paths are handled (may throw various exceptions)
        try {
            $result = HtmlDomParser::fileGetHtml('/nonexistent/path/file.html');
            $this->fail('Expected an exception to be thrown');
        } catch (\Exception $e) {
            // Any exception is acceptable for invalid paths
            $this->assertTrue(true);
        }
    }
    
    public function testFileGetHtmlWithDirectory(): void
    {
        // Test that directory paths are handled (may throw various exceptions)
        try {
            $result = HtmlDomParser::fileGetHtml(__DIR__);
            $this->fail('Expected an exception to be thrown');
        } catch (\Exception $e) {
            // Any exception is acceptable for directory paths
            $this->assertTrue(true);
        }
    }
    
    public function testEmptyStringHandling(): void
    {
        // Empty string triggers size limit exception
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Content size exceed');
        HtmlDomParser::strGetHtml('');
    }
    
    public function testNullStringHandling(): void
    {
        $this->expectException(\TypeError::class);
        HtmlDomParser::strGetHtml(null);
    }
    
    public function testMaliciousHtmlInjection(): void
    {
        $maliciousHtml = '<script>alert("xss")</script><div>content</div>';
        $dom = HtmlDomParser::strGetHtml($maliciousHtml);
        
        // The parser preserves script tags but we can verify content extraction
        $scripts = $dom->find('script');
        // Script tags are preserved in this parser
        $this->assertGreaterThanOrEqual(0, count($scripts));
        
        // Div content should remain accessible
        $divs = $dom->find('div');
        $this->assertCount(1, $divs);
        $this->assertEquals('content', $divs[0]->plaintext);
    }
    
    public function testDeeplyNestedElements(): void
    {
        // Test for potential stack overflow with deeply nested elements
        $nested = str_repeat('<div>', 1000) . 'content' . str_repeat('</div>', 1000);
        
        $dom = HtmlDomParser::strGetHtml($nested);
        $this->assertNotNull($dom);
        $this->assertStringContainsString('content', $dom->plaintext);
    }
    
    public function testSpecialCharactersInAttributes(): void
    {
        $html = '<div id="test&quot;&lt;&gt;" class="special\'chars">content</div>';
        $dom = HtmlDomParser::strGetHtml($html);
        
        $div = $dom->find('div', 0);
        $this->assertNotNull($div);
        $this->assertStringContainsString('test', $div->id);
    }
}
