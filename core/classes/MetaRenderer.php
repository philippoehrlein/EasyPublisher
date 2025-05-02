<?php
/**
 * MetaRenderer - Extracts and processes metadata for the HTML head
 */
class MetaRenderer {
    private $metaContent;
    
    public function __construct() {
        // Load meta.txt, if it exists
        $metaPath = CONTENT_PATH . '/info/meta.txt';
        if (file_exists($metaPath)) {
            $this->metaContent = file_get_contents($metaPath);
        } else {
            $this->metaContent = null;
        }
    }
    
    /**
     * Returns general metadata for the HTML head
     *
     * @return array The metadata as an array
     */
    public function getGlobalMeta() {
      if (!$this->metaContent) {
          return [];
      }
  
      $meta = [];
  
      // Extract the title (first heading)
      if (preg_match('/^#\s+(.+)$/m', $this->metaContent, $matches)) {
          $meta['title'] = trim($matches[1]);
      }
  
      // Extract @key value lines directly under the H1
      $lines = preg_split('/\R/', $this->metaContent);
      $foundH1 = false;
      foreach ($lines as $line) {
          if (!$foundH1) {
              if (preg_match('/^#\s+.+$/', $line)) {
                  $foundH1 = true;
              }
              continue;
          }
  
          if (preg_match('/^@([a-z]+)\s+(.+)$/', $line, $match)) {
              $key = $match[1];
              $value = trim($match[2]);
              $meta[$key] = $value;
          } else {
              break; // Stop on first non-meta line (e.g. ## TOC)
          }
      }
  
      return $meta;
  }

  public function getGlobalTitle() {
    if (!$this->metaContent) {
      return '';
    }

    if (preg_match('/^#\s+(.+)$/m', $this->metaContent, $matches)) {
      return trim($matches[1]);
    }

      return '';
    
  }
    
    /**
     * Returns specific metadata for a specific page
     *
     * @param string $slug The slug of the page
     * @return array The metadata as an array
     */
    public function getPageMeta($slug) {
        if (!$this->metaContent || empty($slug)) {
            return [];
        }
        
        $meta = [];
        
        // Entfernen von .txt-Endung, falls vorhanden
        $slug = preg_replace('/\.txt$/', '', $slug);
        
        // Die meta.txt hat ein spezielles Format: [[WikiLink]]\n@key value
        // Wir müssen also für jeden WikiLink den zugehörigen Metabereich extrahieren
        if (preg_match('/\[\[([^\]|]+)(?:\|[^\]]+)?\]\](.*?)(?=\[\[|\z)/s', $this->metaContent, $matches, PREG_OFFSET_CAPTURE)) {
            // Durchlaufe alle gefundenen WikiLinks
            $offset = 0;
            do {
                $linkName = trim($matches[1][0]);
                $metaSection = trim($matches[2][0]);
                
            
                // Prüfe ob dieser Link zum gesuchten Slug passt
                if (strtolower($linkName) === strtolower($slug) && $metaSection !== '') {
   
                    
                    // Extrahiere die Metadaten aus dem Bereich nach dem Link
                    preg_match_all('/^@([a-z]+)\s+(.+)$/m', $metaSection, $metaMatches, PREG_SET_ORDER);
                    
                    foreach ($metaMatches as $match) {
                        $key = $match[1];
                        $value = trim($match[2]);
                        $meta[$key] = $value;
                    }
                    
                    // Gefunden, Suche beenden
                    break;
                }
                
                // Setze den Offset für die nächste Suche
                $offset = $matches[0][1] + strlen($matches[0][0]);
                
            } while (preg_match('/\[\[([^\]|]+)(?:\|[^\]]+)?\]\](.*?)(?=\[\[|\z)/s', $this->metaContent, $matches, PREG_OFFSET_CAPTURE, $offset));
        }
        return $meta;
    }
    
    /**
     * Renders the HTML meta tags for the head section
     *
     * @param string $currentSlug The current page slug (optional)
     * @return string The HTML meta tags
     */
    public function renderMetaTags($currentSlug = null) {
        // Get general metadata
        $globalMeta = $this->getGlobalMeta();
        $currentSlug = getFileNameBySlug($currentSlug);        
        
        // Get page-specific metadata, if a slug is provided
        $pageMeta = [];
        if ($currentSlug) {
            // Entferne .txt-Endung vom Slug, falls vorhanden
            $currentSlug = preg_replace('/\.txt$/', '', $currentSlug);
            $pageMeta = $this->getPageMeta($currentSlug);
            
        }
        
        $meta = array_merge($globalMeta, $pageMeta);
        
        // Create HTML meta tags
        $html = '';
        
        // Further general meta tags
        foreach ($meta as $key => $value) {
            // Add custom meta tags
            if ($key === 'title') continue;
            $html .= '<meta name="' . htmlspecialchars($key) . '" content="' . htmlspecialchars($value) . '">' . PHP_EOL;
        }
        
        return $html;
    }

    /**
     * Returns the meta tags as an array (instead of rendering HTML)
     *
     * @param string $currentSlug The current page slug (optional)
     * @return array Array of ['name' => ..., 'content' => ...]
     */
    public function getMetaTagArray($currentSlug = null) {
        $globalMeta = $this->getGlobalMeta();
        $currentSlug = getFileNameBySlug($currentSlug);        

        $pageMeta = [];
        if ($currentSlug) {
            $currentSlug = preg_replace('/\.txt$/', '', $currentSlug);
            $pageMeta = $this->getPageMeta($currentSlug);
        }

        $meta = array_merge($globalMeta, $pageMeta);

        $tagArray = [];
        foreach ($meta as $key => $value) {
            $tagArray[$key] = $value;
        }

        return $tagArray;
    }
}
