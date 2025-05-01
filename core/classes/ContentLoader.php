<?php
/**
 * ContentLoader - Loads and processes the content from the Markdown files
 */
class ContentLoader {
    private $markdownParser;
    
    public function __construct() {
        // Initialize the Markdown parser
        require_once CORE_PATH . '/classes/MarkdownParser.php';
        $this->markdownParser = new MarkdownParser();
    }
    
    /**
     * Gets the content by slug
     *
     * @param string $slug The slug of the page
     * @return array|null The content or null if not found
     */
    public function getContentBySlug($slug) {
        // First check in the info folder
        $infoPath = CONTENT_PATH . '/info/' . $slug . '.txt';
        if (file_exists($infoPath)) {
            return $this->parseContentFile($infoPath);
        }
        
        // Then search in the main directory
        $files = glob(CONTENT_PATH . '/*.txt');
        
        // Search all files and apply the correct slug logic
        foreach ($files as $file) {
            $filename = basename($file);
            $fileSlug = getSlugFromFilename($filename);
            
            // Check if the generated slug matches
            if ($fileSlug === $slug) {
                return $this->parseContentFile($file);
            }
        }
        
        return null;
    }
    
    /**
     * Returns all content, either in the order from meta.txt or alphabetically
     *
     * @return array List of all content
     */
    public function getAllContent() {
        $result = [];
        
        // All text files in the Content directory
        $files = glob(CONTENT_PATH . '/*.txt');
        
        // Get the WikiLinks from meta.txt
        $tocEntries = $this->getTocFromMeta();
        
        if ($tocEntries && !empty($tocEntries)) {
            // First collect the sorted files according to the TOC
            $sortedFiles = [];
            $remainingFiles = $files;
            
            foreach ($tocEntries as $entry) {
                $entry = trim($entry);
                
                // Search all files for matches
                foreach ($remainingFiles as $key => $file) {
                    $fileName = basename($file, '.txt');
                    
                    // Check for exact match or with prefix (case insensitive)
                    if (strcasecmp($fileName, $entry) === 0 || 
                        preg_match('/^\d+\-' . preg_quote($entry, '/') . '$/i', $fileName)) {
                        $sortedFiles[] = $file;
                        unset($remainingFiles[$key]);
                        break;
                    }
                }
            }
            
            // Add the remaining files
            sort($remainingFiles);
            $files = array_merge($sortedFiles, $remainingFiles);
            
        } else {
            // Without TOC: Sort files alphabetically
            sort($files);
        }

        foreach ($files as $file) {
            // Use parseContentFile to use consistent slugs
            $content = $this->parseContentFile($file);
            if ($content && !isReservedPath($content['slug'])) {
                $result[] = $content;
            }
        }
        return $result;
    }
    
    
    /**
     * Extracts the TOC information from meta.txt
     *
     * @return array|null List of slugs in the desired order or null
     */
    private function getTocFromMeta() {
        $metaPath = CONTENT_PATH . '/info/meta.txt';
        if (!file_exists($metaPath)) {
            return null;
        }
        
        $metaContent = file_get_contents($metaPath);
        if (!$metaContent) {
            return null;
        }
        
        // Search for the TOC section
        if (!preg_match('/^## TOC\s*$(.*?)(?:^##|\z)/ms', $metaContent, $matches)) {
            return null;
        }
        
        $tocContent = trim($matches[1]);
        
        // Extract the WikiLinks as order
        preg_match_all('/\[\[([^\]|]+)(?:\|[^\]]+)?\]\]/', $tocContent, $wikiMatches);
        
        return $wikiMatches[1];
    }
    
    /**
     * Processes a content file
     *
     * @param string $filePath The file path
     * @return array The processed content
     */
    private function parseContentFile($filePath) {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return null;
        }
        
        // Extract the title
        $title = extractTitle($content);
        
        // Convert Markdown to HTML
        $html = $this->markdownParser->parse($content);
        
        // Generate the slug
        $slug = getSlugFromFilename(basename($filePath));
        
        return [
            'title' => $title ?? basename($filePath),
            'content' => $content,  // Original Markdown
            'html' => $html,        // Converted HTML
            'slug' => $slug,
            'path' => $filePath,
            'readingTime' => getReadingTime($content),
        ];
    }
} 