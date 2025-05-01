<?php
/**
 * Router - Processes the URL requests and forwards them to the correct content
 */
class Router {
    private $requestPath;
    private $contentLoader;
    private $templateRenderer;
    private $metaRenderer;
    
    public function __construct() {
        // Extract the request path from the URL
        $this->requestPath = $this->getRequestPath();
        
        // Initialize the ContentLoader (required for file access)
        require_once CORE_PATH . '/classes/ContentLoader.php';
        $this->contentLoader = new ContentLoader();
        
        // Initialize the TemplateRenderer
        require_once CORE_PATH . '/classes/TemplateRenderer.php';
        $this->templateRenderer = new TemplateRenderer();
        
        // Initialize the MetaRenderer
        require_once CORE_PATH . '/classes/MetaRenderer.php';
        $this->metaRenderer = new MetaRenderer();
    }
    
    /**
     * Extracts the request path from the URL
     */
    private function getRequestPath() {
        $path = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        
        // Remove query parameters
        $path = parse_url($path, PHP_URL_PATH);
        
        // Remove the base URL, if it exists
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/') {
            $path = str_replace($scriptName, '', $path);
        }
        
        // Remove leading and trailing slashes
        $path = trim($path, '/');
        
        // If the path is empty, set it to 'index'
        if (empty($path)) {
            $path = 'index';
        }
        
        return $path;
    }
    
    /**
     * Processes the request and forwards it to the appropriate handler
     */
    public function route() {
        // Check if the index page is requested
        if ($this->requestPath === 'index') {
            $this->renderIndex();
            return;
        }
        
        // Check if a specific page is requested
        $content = $this->contentLoader->getContentBySlug($this->requestPath);
        
        if ($content) {
            $this->renderPage($content);
        } else {
            $this->render404();
        }
    }
    
    /**
     * Renders the index page
     */
    private function renderIndex() {
        // Check if a custom index.txt exists
        $customIndex = $this->contentLoader->getContentBySlug('index');
        
        if ($customIndex) {
            // Render the custom index page with the content of index.txt
            $this->templateRenderer->render('index', [
                'title' => $customIndex['title'],
                'customContent' => $customIndex['html'],
                'metaTags' => $this->metaRenderer->renderMetaTags('index')
            ]);
        } else {
            // Generate the standard content directory
            $contentList = $this->contentLoader->getAllContent();
            $this->templateRenderer->render('index', [
                'title' => 'Homepage',
                'contentList' => $contentList,
                'metaTags' => $this->metaRenderer->renderMetaTags()
            ]);
        }
    }
    
    /**
     * Renders a content page
     */
    private function renderPage($content) {
        $globalTitle = $this->metaRenderer->getGlobalTitle();
        $contentList = $this->contentLoader->getAllContent();
        $this->templateRenderer->render('article', [
            'title' => $globalTitle . ' - ' . $content['title'],
            'content' => $content['html'],
            'contentList' => $contentList,
            'metaTags' => $this->metaRenderer->renderMetaTags($content['slug'])
        ]);
    }
    
    /**
     * Renders a 404 page
     */
    private function render404() {
        header("HTTP/1.0 404 Not Found");
        
        // Try to load the custom 404 file
        $custom404 = $this->contentLoader->getContentBySlug('404');
        if ($custom404) {
            $title = $custom404['title'];
            $this->templateRenderer->render('article', [
                'title' => $title,
                'content' => $custom404['html'],
                'metaTags' => $this->metaRenderer->renderMetaTags('404')
            ]);
            return;
        }
        
        // If no custom 404 file exists, create a standard 404 Markdown file
        $title = 'Page not found';
        
        // Create a temporary Markdown file in memory
        $tempContent = "# $title\n\nThe requested page was not found.\n\nBack to [[index|Homepage]]";
        
        // Use the existing ContentLoader, which already processes the WikiLinks
        require_once CORE_PATH . '/classes/MarkdownParser.php';
        $markdownParser = new MarkdownParser();
        $html = $markdownParser->parse($tempContent);
        
        // Render as an article
        $this->templateRenderer->render('article', [
            'title' => $title,
            'content' => $html,
            'metaTags' => $this->metaRenderer->renderMetaTags()
        ]);
    }
} 