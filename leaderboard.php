<?php
header('Content-Type: application/json');

$xmlFile = 'quiz.xml';

// Load the XML file
if (!file_exists($xmlFile)) {
    $xml = new SimpleXMLElement('<leaderboard></leaderboard>');
    $xml->asXML($xmlFile);
}

// Handle POST request to submit a new score
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $answers = $_POST['answers']; // This is for future use if needed

    // Calculate the score based on the answers
    $correctAnswers = ['B', 'A', 'C', 'B', 'A', 'D', 'C', 'B', 'A', 'D'];
    $score = 0;

    foreach ($answers as $index => $answer) {
        if ($answer === $correctAnswers[$index]) {
            $score++;
        }
    }

    // Add new score to XML
    $xml = simplexml_load_file($xmlFile);
    $entry = $xml->addChild('entry');
    $entry->addChild('name', $name);
    $entry->addChild('score', $score);

    // Save the updated XML file
    $xml->asXML($xmlFile);
    
    // Return the new score as a JSON response
    echo json_encode(['name' => $name, 'score' => $score]);
    exit;
}

// Handle GET request to retrieve the leaderboard
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $xml = simplexml_load_file($xmlFile);
    $entries = [];

    foreach ($xml->entry as $entry) {
        $entries[] = [
            'name' => (string)$entry->name,
            'score' => (int)$entry->score,
        ];
    }

    // Sort entries by score in descending order
    usort($entries, function($a, $b) {
        return $b['score'] <=> $a['score'];
    });

    // Assign ranks
    foreach ($entries as $index => &$entry) {
        $entry['rank'] = $index + 1;
    }

    // Return the leaderboard as JSON
    echo json_encode($entries);
    exit;
}
?>