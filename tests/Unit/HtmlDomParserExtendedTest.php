<?php

namespace Unit;

use Tlab\HtmlDomParser\HtmlDomParser;
use PHPUnit\Framework\TestCase;

class HtmlDomParserExtendedTest extends TestCase
{
    private HtmlDomParser $parser;
    
    protected function setUp(): void
    {
        $this->parser = new HtmlDomParser();
    }
    
    public function testInstanceMethods(): void
    {
        // Test instance method strGetHtml
        $html = '<div id="test">Instance method</div>';
        $dom = $this->parser->strGetHtml($html);
        $this->assertEquals('Instance method', $dom->find('#test', 0)->plaintext);
        
        // Test file_get_html with a fixture file
        $fixtureFile = __DIR__ . '/../fixtures/sample-documents.html';
        if (file_exists($fixtureFile)) {
            $dom = $this->parser->fileGetHtml($fixtureFile);
            $this->assertNotNull($dom);
            $this->assertNotEmpty($dom->find('body'));
        } else {
            $this->markTestSkipped('Sample HTML fixture file not found');
        }
    }
    
    public function testParsingWithDifferentInputTypes(): void
    {
        // Test with string input
        $html = '<div>String input</div>';
        $dom = $this->parser->strGetHtml($html);
        $this->assertEquals('String input', $dom->find('div', 0)->plaintext);
        
        // Test with file input using a temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'html_test_');
        file_put_contents($tempFile, '<div>File input</div>');
        $dom = $this->parser->fileGetHtml($tempFile);
        $this->assertEquals('File input', $dom->find('div', 0)->plaintext);
        unlink($tempFile);
    }
    
    public function testParsingWithOptions(): void
    {
        // Test with lowercase option
        $html = '<DIV ID="test">Mixed case</DIV>';
        
        // Default behavior (case-sensitive)
        $dom = $this->parser->strGetHtml($html, false);
        $this->assertEmpty($dom->find('div')); // Won't find lowercase 'div'
        $this->assertNotEmpty($dom->find('DIV')); // Will find uppercase 'DIV'
        
        // With lowercase option
        $dom = $this->parser->strGetHtml($html, true);
        $this->assertNotEmpty($dom->find('div')); // Will find 'div' because tags are converted to lowercase
        $this->assertEquals('test', $dom->find('div', 0)->getAttribute('id'));
    }
    
    public function testEmptyInputHandling(): void
    {
        try {
            // Test with empty input
            $dom = $this->parser->strGetHtml('');
            $this->assertNotNull($dom);
            $this->assertEmpty($dom->find('*'));
        } catch (\Exception $e) {
            // If we get an exception about content size, that's also acceptable
            $this->assertStringContainsString('Content size', $e->getMessage());
        }
    }
    
    public function testMalformedHtml(): void
    {
        // Test with malformed HTML
        $malformedHtml = '<div><span>Unclosed span</div>';
        $dom = $this->parser->strGetHtml($malformedHtml);
        $this->assertNotNull($dom);
        $this->assertNotEmpty($dom->find('div'));
        $this->assertNotEmpty($dom->find('span'));
    }
    
    public function testNullInput(): void
    {
        // Skip this test as null input is not supported
        $this->markTestSkipped('null input is not supported by the parser');
    }
    
    public function testFileHandling(): void
    {
        try {
            // Test with non-existent file
            $nonExistentFile = __DIR__ . '/non-existent-file.html';
            $this->parser->fileGetHtml($nonExistentFile);
            $this->fail('Exception was not thrown for non-existent file');
        } catch (\Exception $e) {
            $this->assertStringContainsString('file_get_contents', $e->getMessage());
        }
    }
}
