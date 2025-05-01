<?php
/**
 * TemplateRenderer - Renders templates with passed variables
 */
class TemplateRenderer {
    private $layoutFile;
    
    public function __construct() {
        // Set the standard layout file
        $this->layoutFile = CORE_PATH . '/templates/layout.php';
        
        // If a custom theme layout exists, use it
        $themeLayout = THEME_PATH . '/templates/layout.php';
        if (file_exists($themeLayout)) {
            $this->layoutFile = $themeLayout;
        }
    }
    
    /**
     * Renders a template with passed variables
     *
     * @param string $template The name of the template
     * @param array $variables The variables to pass
     */
    public function render($template, $variables = []) {
        // Extract variables into the global scope
        extract($variables);
        
        // Start the content buffer
        ob_start();
        
        // Load the template
        $templateFile = $this->getTemplateFile($template);
        if (file_exists($templateFile)) {
            include $templateFile;
        } else {
            echo "Template not found: " . htmlspecialchars($template);
        }
        
        // Get the content buffer
        $content = ob_get_clean();
        
        // Load the layout
        include $this->layoutFile;
    }
    
    /**
     * Loads a snippet and returns it
     *
     * @param string $snippet The name of the snippet
     * @param array $variables The variables to pass
     * @return string The rendered snippet
     */
    public function snippet($snippet, $variables = []) {
        // Extract variables into the global scope
        extract($variables);
        
        // Start the snippet buffer
        ob_start();
        
        // Load the snippet
        $snippetFile = $this->getSnippetFile($snippet);
        if (file_exists($snippetFile)) {
            include $snippetFile;
        } else {
            echo "Snippet not found: " . htmlspecialchars($snippet);
        }
        
        // Return the snippet buffer
        return ob_get_clean();
    }

    public function icon($icon) {
        $iconFile = THEME_PATH . '/assets/icons/' . $icon . '.svg';
        if (file_exists($iconFile)) {
            return file_get_contents($iconFile);
        }
        
        $iconFile = CORE_PATH . '/assets/icons/' . $icon . '.svg';
        if (file_exists($iconFile)) {
            return file_get_contents($iconFile);
        }

        return '';
    }
    
    /**
     * Gets the path to the template file (checks first in the theme, then in the core)
     *
     * @param string $template The name of the template
     * @return string The path to the template
     */
    private function getTemplateFile($template) {
        // First check in the theme directory
        $themeFile = THEME_PATH . '/templates/' . $template . '.php';
        if (file_exists($themeFile)) {
            return $themeFile;
        }
        
        // Otherwise load from the core
        return CORE_PATH . '/templates/' . $template . '.php';
    }
    
    /**
     * Gets the path to the snippet file (checks first in the theme, then in the core)
     *
     * @param string $snippet The name of the snippet
     * @return string The path to the snippet
     */
    private function getSnippetFile($snippet) {
        // First check in the theme directory
        $themeFile = THEME_PATH . '/snippets/' . $snippet . '.php';
        if (file_exists($themeFile)) {
            return $themeFile;
        }
        
        // Otherwise load from the core
        return CORE_PATH . '/snippets/' . $snippet . '.php';
    }
} 