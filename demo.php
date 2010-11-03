<?php
require_once 'Textwalker.php';
$text = file_get_contents('slashdot.html');

$walker = new Textwalker($text);

# Loop, finding some markup that precedes every news item
while ($walker->nextMatch('/skin topic main tag/' )) {

    # Move the cursor to the place before the news item
    $walker->gotoNext('/skin topic main tag/' );

    # Go to the next link. Ignoring it for now.
    $walker->gotoNext('/href="/');

    # Skipping this link, go to the next one, and make it a bookmark named 
    # 'start_link'.
    $walker->gotoNext('/href="/')->bookmark('start_link');

    # Go to the markup following the URL and make it a bookmark name 'end_link'
    $walker->gotoNext('/" class="datitle">/')->bookmark('end_link');

    # The title starts right after the "end_link" bookmark. Now for the markup 
    # following the end of the title
    $walker->gotoNext('/<\/a>/')->bookmark('end_title');

    # The text between 'start_link' and 'end_link' is the URL. Echoing it.
    echo "Link:  " . $walker->between('start_link','end_link')->getText() . "\n";

    # The text between 'end_link' and 'end_title' is the title. Echoing it.
    echo "Title: " . $walker->between('end_link','end_title')->getText() . "\n";
    echo "\n";
}
