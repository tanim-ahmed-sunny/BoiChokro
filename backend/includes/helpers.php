<?php
/**
 * Resolves an image path by checking multiple possible locations.
 * Inconsistent storage (root uploads vs backend uploads) is handled here.
 */
if (!function_exists('resolve_image_path')) {
    function resolve_image_path($path) {
        if (empty($path)) return '';
        
        // If it's already an absolute URL, return as is
        if (strpos($path, 'http') === 0) return $path;
        
        $path = ltrim($path, '/');
        
        // Check if file exists in root (e.g., uploads/community/xxx.jpg)
        if (file_exists(__DIR__ . '/../../' . $path)) {
            return $path;
        }
        
        // Check if prepending 'backend/' makes it valid (e.g., backend/uploads/community/xxx.jpg)
        if (strpos($path, 'backend/') !== 0) {
            $backend_path = 'backend/' . $path;
            if (file_exists(__DIR__ . '/../../' . $backend_path)) {
                return $backend_path;
            }
        }
        
        // If the path already starts with backend/ but file not found there, 
        // maybe it was double-prefixed or mismatched in DB.
        // But usually, we just return what we have as a fallback.
        return $path;
    }
}
?>
