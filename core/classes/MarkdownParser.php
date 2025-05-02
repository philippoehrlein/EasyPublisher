<?php
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use Wnx\CommonmarkMarkExtension\MarkExtension;
use PhilippOehrlein\InlineFootnotes\InlineFootnoteExtension;

/**
 * MarkdownParser - Converts Markdown to HTML
 * @author Philipp Oehrlein
 * @version 1.0.0
 */
class MarkdownParser {
    private $parser;
    private $wikiLinkProcessor;
    private $imageProcessor;
    public function __construct() {
        // Include the CommonMark parser via Composer
        $this->initParser();
        
        // Initialize the WikiLink processor
        require_once CORE_PATH . '/classes/WikiLinkProcessor.php';
        $this->wikiLinkProcessor = new WikiLinkProcessor();
        require_once CORE_PATH . '/classes/ImageProcessor.php';
        $this->imageProcessor = new ImageProcessor();
    }
    
    /**
     * Initializes the CommonMark parser with the desired extensions
     */
    private function initParser() {
        // Configure the CommonMark environment
        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
            'heading_permalink' => [
                'html_class' => 'heading-permalink',
                'insert' => 'after',
                'symbol' => '¶',
                'title' => "Permalink",
            ]
        ]);
        
        // Add standard Markdown features
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new StrikethroughExtension());
        $environment->addExtension(new FootnoteExtension());
        $environment->addExtension(new HeadingPermalinkExtension());
        $environment->addExtension(new MarkExtension());

        // Add the inline-footnotes extension (if available)
        $extensionClass = new InlineFootnoteExtension();
        if ($extensionClass instanceof ExtensionInterface) {
            $environment->addExtension($extensionClass);
        }
        

        
        // Create the parser
        $this->parser = new \League\CommonMark\MarkdownConverter($environment);
    }
    
    /**
     * Converts Markdown to HTML
     *
     * @param string $markdown The Markdown text
     * @return string The converted HTML
     */
    public function parse($markdown) {
        // First process WikiLinks
        $markdown = $this->wikiLinkProcessor->processWikiLinks($markdown);
        $markdown = $this->imageProcessor->processImages($markdown);

        // Then convert Markdown to HTML
        $html = $this->parser->convert($markdown)->getContent();
        
        return $html;
    }
} 