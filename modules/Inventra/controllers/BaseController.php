<?php
// controllers/BaseController.php
require_once __DIR__ . '/../../../core/Security.php';
// Remove the direct inclusion of main.php here, it's done later
// require_once __DIR__ . '/../views/layout/main.php';

abstract class BaseController {

    protected function render($viewPath, $data = []) {
        extract($data); // Makes variables in $data available in the view scope
        
        // First try to find the view in the module's views directory
        $moduleViewFile = __DIR__ . '/../views/' . $viewPath . '.php';
        // Fall back to the generic views directory
        $genericViewFile = __DIR__ . '/../../../views/pages/' . $viewPath . '.php';
        
        if (file_exists($moduleViewFile)) {
            $viewFile = $moduleViewFile;
        } elseif (file_exists($genericViewFile)) {
            $viewFile = $genericViewFile;
        } else {
            throw new Exception("View file not found. Checked: $moduleViewFile and $genericViewFile");
        }
        
        ob_start(); // Start output buffering
        require_once $viewFile; // Include the view file, its output goes to the buffer
        $content = ob_get_clean(); // Get the buffer contents and clean (end) the buffer
        return $content; // Return the content string
    }

    protected function loadLayout($content, $title = SITE_NAME) {
        $layoutFile = __DIR__ . '/../../../views/layout/main.php';
        if (file_exists($layoutFile)) {
            // Prepare data to be extracted into the layout's scope
            $layoutData = [
                'content' => $content, // This will become the $content variable in main.php
                'title' => $title      // This will become the $title variable in main.php
            ];

            // Extract the layout data into the current scope
            extract($layoutData);

            // Now include the layout file, which will have access to $content and $title
            require_once $layoutFile;
        } else {
            throw new Exception("Layout file not found: $layoutFile");
        }
    }
}