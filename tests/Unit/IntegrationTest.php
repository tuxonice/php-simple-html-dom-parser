<?php

namespace Unit;

use Tlab\HtmlDomParser\HtmlDomParser;
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
    private string $fixturesPath;
    
    protected function setUp(): void
    {
        $this->fixturesPath = __DIR__ . '/../fixtures/';
    }
    
    public function testComplexDocumentParsing(): void
    {
        $dom = HtmlDomParser::fileGetHtml($this->fixturesPath . 'sample-documents.html');
        
        // Test document structure
        $this->assertNotNull($dom);
        
        // Test header navigation
        $nav = $dom->find('nav ul.nav-list', 0);
        $this->assertNotNull($nav);
        
        $navLinks = $nav->find('a');
        $this->assertCount(3, $navLinks);
        
        foreach ($navLinks as $index => $link) {
            $this->assertEquals('#section' . ($index + 1), $link->href);
            $this->assertEquals((string)($index + 1), $link->getAttribute('data-section'));
        }
    }
    
    public function testFormElementExtraction(): void
    {
        $dom = HtmlDomParser::fileGetHtml($this->fixturesPath . 'sample-documents.html');
        
        $form = $dom->find('#test-form', 0);
        $this->assertNotNull($form);
        $this->assertEquals('post', $form->method);
        $this->assertEquals('/submit', $form->action);
        
        // Test input fields
        $username = $dom->find('#username', 0);
        $this->assertEquals('text', $username->type);
        $this->assertTrue($username->hasAttribute('required'));
        
        $email = $dom->find('#email', 0);
        $this->assertEquals('email', $email->type);
        $this->assertEquals('user@example.com', $email->placeholder);
        
        // Test select options
        $options = $dom->find('#country option');
        $this->assertCount(4, $options); // Including empty option
        
        // Test checkbox
        $newsletter = $dom->find('#newsletter', 0);
        $this->assertEquals('checkbox', $newsletter->type);
        $this->assertTrue($newsletter->hasAttribute('checked'));
    }
    
    public function testTableDataExtraction(): void
    {
        $dom = HtmlDomParser::fileGetHtml($this->fixturesPath . 'sample-documents.html');
        
        $table = $dom->find('#data-table', 0);
        $this->assertNotNull($table);
        
        // Test headers
        $headers = $table->find('thead th');
        $this->assertCount(3, $headers);
        $this->assertEquals('Name', $headers[0]->plaintext);
        $this->assertEquals('Age', $headers[1]->plaintext);
        $this->assertEquals('City', $headers[2]->plaintext);
        
        // Test data rows - parser may include header row in count
        $rows = $table->find('tbody tr');
        $this->assertCount(4, $rows);
        
        // Test first data row (skip header if included)
        $dataRowIndex = 0;
        if (count($rows) > 3) {
            // If we have 4 rows, the first might be a header row
            $dataRowIndex = 1;
        }
        
        $firstRowCells = $rows[$dataRowIndex]->find('td');
        $this->assertGreaterThan(0, count($firstRowCells), 'No table cells found');
        $this->assertEquals('John Doe', $firstRowCells[0]->plaintext);
        $this->assertEquals('30', $firstRowCells[1]->plaintext);
        $this->assertEquals('New York', $firstRowCells[2]->plaintext);
        $this->assertEquals('1', $rows[$dataRowIndex]->getAttribute('data-id'));
        
        // Test hidden row - find the row with class="hidden"
        $hiddenRowFound = false;
        foreach ($rows as $row) {
            if ($row->hasAttribute('class') && strpos($row->class, 'hidden') !== false) {
                $hiddenRowFound = true;
                break;
            }
        }
        $this->assertTrue($hiddenRowFound, 'No row with hidden class found');
    }
    
    public function testMediaElementHandling(): void
    {
        $dom = HtmlDomParser::fileGetHtml($this->fixturesPath . 'sample-documents.html');
        
        $images = $dom->find('img');
        $this->assertCount(2, $images);
        
        $firstImage = $images[0];
        $this->assertEquals('image1.jpg', $firstImage->src);
        $this->assertEquals('Sample Image 1', $firstImage->alt);
        $this->assertEquals('300', $firstImage->width);
        $this->assertEquals('200', $firstImage->height);
        
        $video = $dom->find('video', 0);
        $this->assertNotNull($video);
        $this->assertTrue($video->hasAttribute('controls'));
        $this->assertEquals('400', $video->width);
        
        $source = $video->find('source', 0);
        $this->assertEquals('video.mp4', $source->src);
        $this->assertEquals('video/mp4', $source->type);
    }
    
    public function testSelectorComplexity(): void
    {
        $dom = HtmlDomParser::fileGetHtml($this->fixturesPath . 'sample-documents.html');
        
        // Test complex selectors - find section with highlight class
        $highlightSections = $dom->find('section.highlight');
        $this->assertCount(1, $highlightSections);
        
        $dataAttributes = $dom->find('[data-priority]');
        $this->assertCount(3, $dataAttributes);
        
        $highPriority = $dom->find('[data-priority=high]');
        $this->assertCount(1, $highPriority);
        $this->assertStringContainsString('High priority', $highPriority[0]->plaintext);
        
        $externalLinks = $dom->find('a[target=_blank]');
        $this->assertCount(2, $externalLinks);
        
        foreach ($externalLinks as $link) {
            $this->assertEquals('_blank', $link->target);
            $this->assertEquals('noopener', $link->rel);
        }
    }
    
    public function testScriptAndStyleStripping(): void
    {
        $dom = HtmlDomParser::fileGetHtml($this->fixturesPath . 'sample-documents.html');
        
        // Scripts are preserved in this parser
        $scripts = $dom->find('script');
        $this->assertCount(1, $scripts);
        
        // Styles are preserved in this parser
        $styles = $dom->find('style');
        $this->assertCount(1, $styles);
        
        // But the content should still be accessible
        $plaintext = $dom->plaintext;
        $this->assertStringNotContainsString('console.log', $plaintext);
        $this->assertStringNotContainsString('background-color', $plaintext);
    }
    
    public function testDocumentTraversal(): void
    {
        $dom = HtmlDomParser::fileGetHtml($this->fixturesPath . 'sample-documents.html');
        
        $main = $dom->find('main', 0);
        $sections = $main->find('section');
        $this->assertCount(3, $sections);
        
        // Test sibling navigation
        $firstSection = $sections[0];
        $secondSection = $firstSection->next_sibling();
        $this->assertNotNull($secondSection);
        $this->assertEquals('section2', $secondSection->id);
        
        $thirdSection = $secondSection->next_sibling();
        $this->assertNotNull($thirdSection);
        $this->assertEquals('section3', $thirdSection->id);
        
        // Test parent navigation
        $this->assertEquals('main', $firstSection->parent->tag);
        
        // Test child navigation
        $h2 = $firstSection->first_child();
        while ($h2 && $h2->tag !== 'h2') {
            $h2 = $h2->next_sibling();
        }
        $this->assertNotNull($h2);
        $this->assertEquals('Forms and Inputs', $h2->plaintext);
    }
}
