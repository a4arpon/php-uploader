<?php
header('Content-Type: application/json');

$response = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['file']) && isset($_POST['directory']) && isset($_POST['user_name'])) {
        // Base URL from .htaccess file or hardcoded as needed
        $baseUrl = getenv('BASE_URL');
        if (!$baseUrl) {
            $baseUrl = $_SERVER['BASE_URL'];
        }
        if (!$baseUrl) {
            $baseUrl = 'http://img-packet.lovestoblog.com'; // Fallback if not defined
        }

        // Generate a slug from the input
        function generateSlug($str) {
            // Convert to lowercase
            $str = strtolower($str);
            // Remove special characters and replace spaces with hyphens
            $str = preg_replace('/[^a-z0-9-]+/', '-', $str);
            // Trim hyphens from both ends
            $str = trim($str, '-');
            return $str;
        }

        // Generate a random 8-character number
        function generateRandomNumber() {
            return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 8);
        }

        $userName = generateSlug($_POST['user_name']);
        $directoryInput = generateSlug($_POST['directory']);
        $targetDir = 'uploads/' . $userName . '/' . $directoryInput;
        $file = $_FILES['file'];

        // Create target directory if it doesn't exist
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Process the file
        if ($file['error'] == 0) {
            $originalFilename = pathinfo($file['name'], PATHINFO_FILENAME);
            $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);

            $slug = generateSlug($originalFilename);
            $randomNumber = generateRandomNumber();
            $newFilename = $slug . '-' . $randomNumber . '.' . $fileExtension;

            $targetFilePath = rtrim($targetDir, '/') . '/' . $newFilename;

            if (move_uploaded_file($file['tmp_name'], $targetFilePath)) {
                $response['status'] = 'success';
                $response['message'] = 'File uploaded successfully.';
                $response['path'] = $targetFilePath;
                $response['url'] = rtrim($baseUrl, '/') . '/' . $targetFilePath;
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Sorry, there was an error uploading your file.';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Error: ' . $file['error'];
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'No file, directory, or user name specified.';
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
