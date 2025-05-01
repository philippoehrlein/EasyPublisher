<?php
/**
 * Helper functions for easyPublisher
 */

/**
 * Returns a URL for the given path
 *
 * @param string $path The path within the application
 * @return string The full URL
 */
function url($path = '') {
    $path = trim($path, '/');
    $basePath = dirname($_SERVER['SCRIPT_NAME']);
    
    if ($basePath == '/') {
        $basePath = '';
    }
    
    return $basePath . '/' . $path;
}

/**
 * Converts a filename to a URL slug
 *
 * @param string $filename The filename
 * @return string The URL slug
 */
function fileToSlug($filename) {
    // Remove the file extension
    $slug = pathinfo($filename, PATHINFO_FILENAME);
    
    // Remove numeric prefixes like "01-", "02-" etc.
    $slug = preg_replace('/^\d+\-/', '', $slug);
    
    return $slug;
}

/**
 * Returns the title from the content
 * 
 * @param string $content The content
 * @return string The extracted title
 */
function extractTitle($content) {
    // Search for the first heading (# Title)
    if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
        return trim($matches[1]);
    }
    
    return null;
}

/**
 * Checks if a path matches one of the reserved filenames
 *
 * @param string $path The path to check
 * @return bool True if it is a reserved name
 */
function isReservedPath($path) {
    $reservedNames = ['index', 'meta', 'imprint', 'privacy', 'contact', '404'];
    return in_array($path, $reservedNames);
}

/**
 * Converts a title to a SEO-friendly slug
 *
 * @param string $text The text to slugify
 * @return string The SEO-friendly slug
 */
function sanitizeSlug($text) {
    // Zu Kleinbuchstaben umwandeln
    $text = mb_strtolower($text, 'UTF-8');
    
    // Replace umlauts and special characters
    $text = str_replace(
        ['ä',  'ö',  'ü',  'ß',  ' ', '&', ':', ';', ',', '.', '!', '?', '(', ')', '[', ']', '{', '}', '/', '\\', '"', "'", '+', '*', '=', '@', '#', '%', '|', '<', '>', '$', '€', '¥', '£', '~', '^', '–', '—', '―', '‒'],
        ['ae', 'oe', 'ue', 'ss', '-', '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',   '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '-', '-', '-', '-'],
        $text
    );
    
    // Remove all other non-alphanumeric characters except hyphens
    $text = preg_replace('/[^a-z0-9\-]/', '', $text);
    
    // Replace multiple consecutive hyphens with a single hyphen
    $text = preg_replace('/-+/', '-', $text);
    
    // Remove hyphens at the beginning and end
    $text = trim($text, '-');
    
    return $text;
}


/**
 * Generates a slug from a filename, based on the H1 title or filename
 *
 * @param string $filename The filename (without path)
 * @return string|null The generated slug or null if the file does not exist
 */
function getSlugFromFilename($filename) {
    // Path to the file in the content folder
    $filePath = CONTENT_PATH . '/' . $filename;
    
    // Check if the file exists
    if (!file_exists($filePath)) {
        return null;
    }
    
    // Read the content of the file
    $content = file_get_contents($filePath);
    if ($content === false) {
        return null;
    }
    
    // Extract the title
    $title = extractTitle($content);
    
    // Generate a slug from the title or filename
    if (!empty($title)) {
        // Title available - use it as slug
        return sanitizeSlug($title);
    } else {
        // No title - use the filename
        return fileToSlug(basename($filePath));
    }
}

/**
 * Calculates the reading time for a given content
 *
 * @param string $content The content to calculate the reading time for
 * @return string The reading time in minutes
 */
function getReadingTime($content) {
    // Count the words
    $wordCount = str_word_count(strip_tags($content));
    
    // Calculate the reading time
    $readingTime = ceil($wordCount / 150);

    return $readingTime . ' min';
}


/**
 * Returns the filename for a given slug
 *
 * @param string $slug The slug to search for
 * @return string|null The filename or null if not found
 */
function getFileNameBySlug($slug) {
    $files = glob(CONTENT_PATH . '/*.txt');
    foreach ($files as $file) {
        if (getSlugFromFilename(basename($file)) === $slug) {
            return basename($file);
        }
    }
    return null;
}

/**
 * Gibt die HTML-Tags für alle CSS-Plugins zurück
 * 
 * @return string HTML-Tags für CSS-Plugins
 */
function getPluginCssTags() {
    $html = '';
    
    // Theme Plugins
    $pluginFiles = glob(THEME_PATH . '/plugins/*.css');
    foreach ($pluginFiles as $file) {
        $url = 'theme/plugins/' . basename($file);
        $html .= "<link rel=\"stylesheet\" href=\"$url\">\n";
    }

    // Core Plugins
    $pluginFiles = glob(CORE_PATH . '/assets/plugins/*.css');
    foreach ($pluginFiles as $file) {
        $url = 'core/assets/plugins/' . basename($file);
        $html .= "<link rel=\"stylesheet\" href=\"$url\">\n";
    }
    
    return $html;
}

/**
 * Gibt die HTML-Tags für alle JavaScript-Plugins zurück
 * 
 * @return string HTML-Tags für JavaScript-Plugins
 */
function getPluginJsTags() {
    $html = '';
    
    // Core Plugins
    $pluginFiles = glob(CORE_PATH . '/assets/plugins/*.js');
    foreach ($pluginFiles as $file) {
        $url = 'core/assets/plugins/' . basename($file);
        $html .= "<script src=\"$url\"></script>\n";
    }
    
    // Theme Plugins
    $pluginFiles = glob(THEME_PATH . '/plugins/*.js');
    foreach ($pluginFiles as $file) {
        $url = 'theme/plugins/' . basename($file);
        $html .= "<script src=\"$url\"></script>\n";
    }
    
    return $html;
}