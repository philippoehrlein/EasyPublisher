<?php
/**
 * WikiLinkProcessor - Processes WikiLinks in Markdown
 * @author Philipp Oehrlein
 * @version 1.0.0
 */
class WikiLinkProcessor {
    /**
     * Processes WikiLinks in the format [[page]] or [[page|text]]
     *
     * @param string $markdown The Markdown text with WikiLinks
     * @return string The Markdown text with processed WikiLinks
     */
    public function processWikiLinks($markdown) {
        // Find and replace WikiLinks in the format [[link]] or [[link|text]]
        return preg_replace_callback(
            '/\[\[([^\]]+)\]\]/',
            function ($matches) {
                $linkParts = explode('|', $matches[1], 2);
                $target = trim($linkParts[0]);
                $text = isset($linkParts[1]) ? trim($linkParts[1]) : $target;
                
                // Create the link
                return $this->createLink($target, $text);
            },
            $markdown
        );
    }
    
    /**
     * Creates an HTML link for a WikiLink
     *
     * @param string $target The target page
     * @param string $text The text to display
     * @return string The HTML link
     */
    private function createLink($target, $text) {
        // Format the target slug
        $slug = $this->formatSlug($target);
        
        // Create the markdown-formatted link
        return "[$text](" . url($slug) . ")";
    }
    
    /**
     * Formatiert einen Slug für die URL
     *
     * @param string $text The text to format
     * @return string The formatted slug
     */
    private function formatSlug($text) {
        // First check if a file with this name exists and generate a slug
        $filename = $text . '.txt';
        $slug = getSlugFromFilename($filename);
        
        // If a slug was generated, use it
        if ($slug !== null) {
            return $slug;
        }
        
        // Otherwise handle the text directly as a slug
        return sanitizeSlug($text);
    }
} 