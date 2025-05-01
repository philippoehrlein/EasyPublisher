<?php

namespace PhilippOehrlein\InlineFootnotes;

use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Event\DocumentPreParsedEvent;
use League\CommonMark\Input\MarkdownInput;

/**
 * Extension for inline footnotes in the format [^Footnote text]
 * 
 * This extension detects and converts inline footnotes to standard Markdown footnotes
 * before the actual parsing process. This also correctly supports links and complex content
 * in footnotes.
 */
class InlineFootnoteExtension implements ExtensionInterface
{
    /**
     * Registers the extension in the CommonMark environment
     */
    public function register(EnvironmentBuilderInterface $environment): void
    {
        // Adds an event listener that is called before parsing the document
        $environment->addEventListener(
            DocumentPreParsedEvent::class, 
            [$this, 'convertInlineFootnotes']
        );
    }
    
    /**
     * Converts inline footnotes to standard Markdown footnotes
     * 
     * This method is called before parsing the document and replaces
     * all inline footnotes with standard-compliant footnotes.
     */
    public function convertInlineFootnotes(DocumentPreParsedEvent $event): void
    {
        // Gets the original Markdown text
        $markdown = $event->getMarkdown();
        $text = $markdown->getContent();
        
        // Collect all defined classic footnotes
        preg_match_all('/\[\^([a-zA-Z0-9_\-:\.]+)\]:/', $text, $definitions);
        $definedIds = array_flip($definitions[1]);
        
        // Collect all footnotes
        $footnotes = [];
        $counter = 1;
        
        // Don't convert regular footnotes first
        $positions = [];
        
        // Temporarily protect regular footnote references by marking them
        $text = preg_replace_callback(
            '/\[\^([a-zA-Z0-9_\-:\.]+)\](?!:)/',
            function ($matches) use (&$positions, $definedIds) {
                $id = $matches[1];
                if (!isset($definedIds[$id])) {
                    return $matches[0]; // Not defined → don't protect
                }
                $posId = count($positions);
                $positions[$posId] = $matches[0];
                return "%%FOOTNOTE_PLACEHOLDER_$posId%%";
            },
            $text
        );
        
        // Find all inline footnotes with a manual method that better handles
        // nested brackets
        $convertedText = $this->processInlineFootnotes($text, $footnotes, $counter);
        
        // Restore the protected footnote references
        $convertedText = preg_replace_callback(
            '/%%FOOTNOTE_PLACEHOLDER_(\d+)%%/',
            function ($matches) use ($positions) {
                return $positions[(int)$matches[1]];
            },
            $convertedText
        );
        
        // Replace the original Markdown text with the converted text
        $event->replaceMarkdown(new MarkdownInput($convertedText));
    }
    
    /**
     * Processes inline footnotes with a manual method to support nested brackets
     */
    private function processInlineFootnotes(string $text, array &$footnotes, int &$counter): string
    {
        $result = [];
        $length = strlen($text);
        $i = 0;

        while ($i < $length) {
            $start = strpos($text, '[^', $i);
            if ($start === false) {
                $result[] = substr($text, $i);
                break;
            }

            $result[] = substr($text, $i, $start - $i);

            // Try to parse nested structure
            $pos = $start + 2;
            $level = 0;
            $content = '';
            $foundEnd = false;

            while ($pos < $length) {
                $char = $text[$pos];

                if ($char === '[') {
                    $level++;
                    $content .= $char;
                } elseif ($char === ']') {
                    if ($level === 0) {
                        $foundEnd = true;
                        break;
                    }
                    $level--;
                    $content .= $char;
                } else {
                    $content .= $char;
                }

                $pos++;
            }

            if (!$foundEnd) {
                // Unclosed footnote
                $result[] = substr($text, $start, $pos - $start);
                $i = $pos;
                continue;
            }

            $footnoteId = 'inline' . $counter++;
            $footnotes[$footnoteId] = $content;
            $result[] = "[^$footnoteId]";

            $i = $pos + 1;
        }

        // Append footnotes
        if (!empty($footnotes)) {
            $result[] = "\n\n";
            foreach ($footnotes as $id => $content) {
                $result[] = "[^$id]: $content\n";
            }
        }

        return implode('', $result);
    }
}