<?php
$videos = array();

for ($i = 3; $i <= 873; $i++) {
    $videos[] = array(
        "src" => "https://cdn.desikahani2.net/0/$i/$i.mp4",
        "title" => "Video $i"
    );
}

// Option 1: Output JSON directly
header('Content-Type: application/json');
echo json_encode($videos, JSON_PRETTY_PRINT);

// Option 2: Save JSON to a file
// file_put_contents('videos.json', json_encode($videos, JSON_PRETTY_PRINT));
?>
