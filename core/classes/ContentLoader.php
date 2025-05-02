<?php
/**
 * ContentLoader - Loads and processes the content from the Markdown files
 * @author Philipp Oehrlein
 * @version 1.0.0
 */
class ContentLoader {
    private $markdownParser;
    private $suffix = 'txt';
    private static $config = null;
    
    public function __construct() {
        // Initialize the Markdown parser
        require_once CORE_PATH . '/classes/MarkdownParser.php';
        $this->markdownParser = new MarkdownParser();
        
        // Load config if not already loaded
        if (self::$config === null) {
            self::$config = $this->loadConfig();
        }
    }
    
    /**
     * Loads the configuration from config.txt
     * 
     * @return array The configuration array
     */
    private function loadConfig() {
        $config = [];
        $configFile = CONTENT_PATH . '/info/config.txt';

        if(!file_exists($configFile)) {
            $configFile = CONTENT_PATH . '/info/config.md';
        }
        
        if (file_exists($configFile)) {
            $content = file_get_contents($configFile);
            $lines = explode("\n", $content);
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || strpos($line, '@') !== 0) {
                    continue;
                }
                
                $parts = explode(' ', $line, 2);
                if (count($parts) === 2) {
                    $key = substr($parts[0], 1); // Entferne das @
                    $value = trim($parts[1]);
                    $config[$key] = $value;
                }
            }
        }

        $this->suffix = $config['suffix'] ?? 'txt';
        return $config;
    }
    
    /**
     * Returns a configuration value or all configuration values
     * 
     * @param string|null $key The configuration key to retrieve
     * @return mixed The configuration value or all values if no key is provided
     */
    public static function getConfig($key = null) {
        if ($key === null) {
            return self::$config ?? [];
        }
        return self::$config[$key] ?? null;
    }
    
    /**
     * Gets the content by slug
     *
     * @param string $slug The slug of the page
     * @return array|null The content or null if not found
     */
    public function getContentBySlug($slug) {
        // First check in the info folder
        $infoPath = CONTENT_PATH . '/info/' . $slug . '.' . $this->suffix;
        if (file_exists($infoPath)) {
            return $this->parseContentFile($infoPath);
        }
        
        // Then search in the main directory
        $files = glob(CONTENT_PATH . '/*.' . $this->suffix);
        
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
        $files = glob(CONTENT_PATH . '/*.' . $this->suffix);
        
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
                    $fileName = basename($file, '.' . $this->suffix);
                    
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
        $metaPath = CONTENT_PATH . '/info/meta.' . $this->suffix;
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