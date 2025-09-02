<?php

namespace Unit;

use Tlab\HtmlDomParser\HtmlDomParser;
use Tlab\HtmlDomParser\SimpleHtmlDom;
use PHPUnit\Framework\TestCase;

class PerformanceTest extends TestCase
{
    private const PERFORMANCE_THRESHOLD_MS = 150; // 150ms threshold - adjusted based on actual performance
    private const MEMORY_THRESHOLD_MB = 10; // 10MB threshold
    
    public function testParsingPerformance(): void
    {
        $html = $this->generateLargeHtml(1000); // 1000 elements
        
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        $dom = HtmlDomParser::strGetHtml($html);
        $elements = $dom->find('div');
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $executionTime = ($endTime - $startTime) * 1000; // Convert to ms
        $memoryUsed = ($endMemory - $startMemory) / 1024 / 1024; // Convert to MB
        
        $this->assertLessThan(self::PERFORMANCE_THRESHOLD_MS, $executionTime, 
            "Parsing took {$executionTime}ms, expected less than " . self::PERFORMANCE_THRESHOLD_MS . "ms");
        
        $this->assertLessThan(self::MEMORY_THRESHOLD_MB, $memoryUsed,
            "Memory usage was {$memoryUsed}MB, expected less than " . self::MEMORY_THRESHOLD_MB . "MB");
        
        $this->assertCount(1000, $elements);
    }
    
    public function testSelectorPerformance(): void
    {
        $html = $this->generateComplexHtml();
        $dom = HtmlDomParser::strGetHtml($html);
        
        $selectors = [
            'div',
            '.class1',
            '#id1',
            'div.class1',
            'div[data-test]',
            'div.class1[data-test=value1]'
        ];
        
        foreach ($selectors as $selector) {
            $startTime = microtime(true);
            $elements = $dom->find($selector);
            $endTime = microtime(true);
            
            $executionTime = ($endTime - $startTime) * 1000;
            
            $this->assertLessThan(50, $executionTime, 
                "Selector '{$selector}' took {$executionTime}ms, expected less than 50ms");
        }
    }
    
    public function testMemoryLeakPrevention(): void
    {
        $initialMemory = memory_get_usage(true);
        
        // Create and destroy multiple DOM objects
        for ($i = 0; $i < 10; $i++) {
            $html = $this->generateLargeHtml(100);
            $dom = HtmlDomParser::strGetHtml($html);
            $elements = $dom->find('div');
            
            // Explicitly clear to test memory management
            $dom->clear();
            unset($dom, $elements);
        }
        
        // Force garbage collection
        gc_collect_cycles();
        
        $finalMemory = memory_get_usage(true);
        $memoryDiff = ($finalMemory - $initialMemory) / 1024 / 1024; // MB
        
        // Memory increase should be minimal after cleanup
        $this->assertLessThan(5, $memoryDiff, 
            "Memory increased by {$memoryDiff}MB after multiple DOM operations");
    }
    
    public function testLargeDocumentHandling(): void
    {
        // Test with a document close to the size limit
        $largeHtml = str_repeat('<div class="item">Content ' . rand(1, 1000) . '</div>', 5000);
        
        $startTime = microtime(true);
        $dom = HtmlDomParser::strGetHtml($largeHtml);
        $endTime = microtime(true);
        
        $executionTime = ($endTime - $startTime) * 1000;
        
        $this->assertLessThan(500, $executionTime, 
            "Large document parsing took {$executionTime}ms, expected less than 500ms");
        
        $elements = $dom->find('.item');
        $this->assertCount(5000, $elements);
    }
    
    private function generateLargeHtml(int $elementCount): string
    {
        $html = '<html><body>';
        for ($i = 0; $i < $elementCount; $i++) {
            $html .= "<div id='div{$i}' class='class" . ($i % 3) . "'>Content {$i}</div>";
        }
        $html .= '</body></html>';
        return $html;
    }
    
    private function generateComplexHtml(): string
    {
        return '
        <html>
        <body>
            <div id="id1" class="class1" data-test="value1">Content 1</div>
            <div id="id2" class="class2" data-test="value2">Content 2</div>
            <div class="class1" data-test="value1">Content 3</div>
            <span class="class1">Span content</span>
            <div class="class1 class2" data-test="value3">Multiple classes</div>
            <div>
                <div class="nested class1" data-test="nested">Nested content</div>
            </div>
        </body>
        </html>';
    }
}
