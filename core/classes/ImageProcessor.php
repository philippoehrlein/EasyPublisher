<?php
/**
 * ImageProcessor - Processes images in the content
 */
class ImageProcessor {
    /**
     * Processes images in the content
     * 
     * @param string $markdown The markdown to process
     * @return string The processed markdown
     */
    public function processImages($markdown) {
      // 1. Classic Markdown Images
      $markdown = preg_replace_callback('/!\[.*?\]\((.*?)\)/', function($matches) {
          $image = $matches[1];
          if ($this->checkImageExists($image)) {
              return $this->replaceImageWithLink($image);
          }
          return $matches[0];
      }, $markdown);
  
      // 2. Only Filenames (e.g. "image.jpg")
      $markdown = preg_replace_callback('/(?<=^|\s)([^\s]+\.(jpg|jpeg|png|gif))(?=\s|$)/i', function($matches) {
          $image = $matches[1];
          if ($this->checkImageExists($image)) {
              return $this->replaceImageWithLink($image);
          }
          return $matches[0];
      }, $markdown);
  
      return $markdown;
  }

    /**
     * Checks if an image exists
     * 
     * @param string $image The image to check
     * @return bool True if the image exists, false otherwise
     */
    private function checkImageExists($image) {
        $folder = CONTENT_PATH;
        return file_exists($folder . '/' . $image);
    }

    /**
     * Replaces the image with a link to the image
     * 
     * @param string $image The image to replace
     * @return string The processed markdown
     */
    private function replaceImageWithLink($image) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $domain = $protocol . '://' . $_SERVER['HTTP_HOST'];
        return '![](' . $domain  . '/content/' . $image . ')';
    }
}

