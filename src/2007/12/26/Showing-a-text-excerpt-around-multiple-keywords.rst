Showing a text excerpt around multiple keywords
===============================================

by grigri on December 26, 2007

On a typical search results page, you want to display an excerpt of
each matching item, showing the keywords. TextHelper::excerpt() works,
but only for a single word/phrase. This helper finds the chunk of text
containing as many keywords as possible.
This is very simple to use : just pass it a chunk of text and an array
of keywords. There are quite a few options for tweaking how a
particular match is valued, but the defaults I use seem to work fine
most of the time.

One thing to note is that the `$length` parameter does not include the
prefix or suffix length.


The Algorithm
-------------
The algorithm is pretty simple; I'll try and explain the concept:

Any given excerpt has a score, which basically corresponds to the
number of keywords present within it.

We don't need to test every possible excerpt, nor even every word
boundary -- we only need to check every position where the score
changes.

The score can only change at certain points (events) -- when a keyword
appears (push event) and when a keyword disappears (pop event).

So, we create a list of the events in the order they occur (event
queue), and process them, cumulatively building up the score (as the
terminology suggests, it's a stack system).

Once this is done, look at each event position and find the one with
the highest score. That's the excerpt to grab.


Notes
-----
This algorithm is a starting point, not a complete solution. I've
found that adding a 'bump' event to centre excerpts around keywords
works quite well, as well as favourising different keywords rather
than repeats of the same one.

I'm planning to extend this with more options, for getting the helper
to 'prefer' excerpts containing whole sentences, and a few others.

The `excerpt()` method does not hilight the keywords in any way; use
in conjunction with TextHelper::highlight() to do that (I might add
this as an option at a later date)

There's a debug option which outputs a table of events and a table of
matches - useful for tweaking settings on test data. The excerpt is
always returned, the debug information is always output.


Code
----

Anyway, here's the code:


Helper Class:
`````````````

::

    <?php 
    class SummaryHelper extends AppHelper {
    
      function excerpt($text, $words, $length=150, $prefix="...", $suffix = null, $options = array()) {
    
        // Set default score modifiers [tweak away...]
        $options = am(array(
          'exact_case_bonus'  => 2,
          'exact_word_bonus'  => 3,
          'abs_length_weight' => 0.0,
          'rel_length_weight' => 1.0,
    
          'debug' => true
        ), $options);
    
        // Null suffix defaults to same as prefix
        if (is_null($suffix)) {
          $suffix = $prefix;
        }
    
        // Not enough to work with?
        if (strlen($text) <= $length) {
          return $text;
        }
    
        // Just in case
        if (!is_array($words)) {
          $words = array($words);
        }
    
        // Build the event list
        // [also calculate maximum word length for relative weight bonus]
        $events = array();
        $maxWordLength = 0;
    
        foreach ($words as $word) {
    
          if (strlen($word) > $maxWordLength) {
            $maxWordLength = strlen($word);
          }
    
          $i = -1;
          while ( ($i = stripos($text, $word, $i+1)) !== false ) {
    
            // Basic score for a match is always 1
            $score = 1;
    
            // Apply modifiers
            if (substr($text, $i, strlen($word)) == $word) {
              // Case matches exactly
              $score += $options['exact_case_bonus'];
            }
            if ($options['abs_length_weight'] != 0.0) {
              // Absolute length weight (longer words count for more)
              $score += strlen($word) * $options['abs_length_weight'];
            }
            if ($options['rel_length_weight'] != 0.0) {
              // Relative length weight (longer words count for more)
              $score += strlen($word) / $maxWordLength * $options['rel_length_weight'];
            }
            if (preg_match('/\W/', substr($text, $i-1, 1))) {
              // The start of the word matches exactly
              $score += $options['exact_word_bonus'];
            }
            if (preg_match('/\W/', substr($text, $i+strlen($word), 1))) {
              // The end of the word matches exactly
              $score += $options['exact_word_bonus'];
            }
    
            // Push event occurs when the word comes into range
            $events[] = array(
              'type'  => 'push',
              'word'  => $word,
              'pos'   => max(0, $i + strlen($word) - $length),
              'score' => $score
            );
            // Pop event occurs when the word goes out of range
            $events[] = array(
              'type' => 'pop',
              'word' => $word,
              'pos'  => $i + 1,
              'score' => $score
            );
            // Bump event makes it more attractive for words to be in the
            // middle of the excerpt [@todo: this needs work]
            $events[] = array(
              'type' => 'bump',
              'word' => $word,
              'pos'  => max(0, $i + floor(strlen($word)/2) - floor($length/2)),
              'score' => 0.5
            );
    
          }
        }
    
        // If nothing is found then just truncate from the beginning
        if (empty($events)) {
          return substr($text, 0, $length) . $suffix;
        }
    
        // We want to handle each event in the order it occurs in
        // [i.e. we want an event queue]
        $events = sortByKey($events, 'pos');
    
        $scores = array();
        $score = 0;
        $current_words = array();
    
        // Process each event in turn
        foreach ($events as $idx => $event) {
          $thisPos = floor($event['pos']);
    
          $word = strtolower($event['word']);
    
          switch ($event['type']) {
          case 'push':
            if (empty($current_words[$word])) {
              // First occurence of a word gets full value
              $current_words[$word] = 1;
              $score += $event['score'];
            }
            else {
              // Subsequent occurrences mean less and less
              $current_words[$word]++;
              $score += $event['score'] / sizeof($current_words[$word]);
            }
            break;
          case 'pop':
            if (($current_words[$word])==1) {
              unset($current_words[$word]);
              $score -= ($event['score']);
            }
            else {
              $current_words[$word]--;
              $score -= $event['score'] / sizeof($current_words[$word]);
            }
            break;
          case 'bump':
            if (!empty($event['score'])) {
              $score += $event['score'];
            }
            break;
          default:
          }
    
          // Close enough for government work...
          $score = round($score, 2);
    
          // Store the position/score entry
          $scores[$thisPos] = $score;
    
          // For use with debugging
          $debugWords[$thisPos] = $current_words;
    
          // Remove score bump
          if ($event['type'] == 'bump') {
              $score -= $event['score'];
          }
        }
    
        // Calculate the best score
        // Yeah, could have done this in the main event loop
        // but it's better here
        $bestScore = 0;
        foreach ($scores as $pos => $score) {
            if ($score > $bestScore) {
              $bestScore = $score;
            }
        }
    
    
        if ($options['debug']) {
          // This is really quick, really tatty debug information
          // (but it works)
          echo "<table border>";
          echo "<caption>Events</caption>";
          echo "<tr><th>Pos</th><th>Type</th><th>Word</th><th>Score</th>";
          foreach ($events as $event) {
            echo "<tr>";
            echo "<td>{$event['pos']}</td><td>{$event['type']}</td><td>{$event['word']}</td><td>{$event['score']}</td>";
            echo "</tr>";
          }
          echo "</table>";
    
          echo "<table border>";
          echo "<caption>Positions and their scores</caption>";
          $idx = 0;
          foreach ($scores as $pos => $score) {
            $excerpt = substr($text, $pos, $length);
            $style = ($score == $bestScore) ? 'background: #ff7;' : '';
    
            //$score = floor($score + 0.5);
    
            echo "<tr>";
            echo "<th style=\"$style\">" . $idx . "</th>";
            echo "<td style=\"$style\">" . $pos . "</td>";
            echo "<td style=\"$style\"><div style=\"float: left; width: 2em; margin-right: 1em; text-align right; background: #ddd\">" . $score . "</div><code>" . str_repeat('*', $score) . "</code></td>";
            echo "<td style=\"$style\"><table border>";
            foreach ($debugWords[$pos] as $word => $count) {
              echo "<tr><td>$word</td><td>$count</td></tr>";
            }
            echo "</table></td>";
            echo "<td style=\"$style\">" . (preg_replace('/(' . implode('|', $words) . ')/i', '<b style="border: 1px solid red;">\1</b>', htmlentities($excerpt))) . "</td>";
            echo "</tr>";
            $idx++;
          }
          echo "</table>";
        }
    
    
        // Find all positions that correspond to the best score
        $positions = array();
        foreach ($scores as $pos => $score) {
          if ($score == $bestScore) {
            $positions[] = $pos;
          }
        }
    
        if (sizeof($positions) > 1) {
          // Scores are tied => do something clever to choose one
          // @todo: Actually do something clever here
          $pos = $positions[0];
        }
        else {
          $pos = $positions[0];
        }
    
        // Extract the excerpt from the position, (pre|ap)pend the (pre|suf)fix
        $excerpt = substr($text, $pos, $length);
        if ($pos > 0) {
          $excerpt = $prefix . $excerpt;
        }
        if ($pos + $length < strlen($text)) {
          $excerpt .= $suffix;
        }
    
        return $excerpt;
      }
    }
    ?>

And here's a sample usage:


View Template:
``````````````

::

    
    <?php echo $summary->excerpt($data['Article']['body'], array('some', 'keywords', 'here')); ?>



.. author:: grigri
.. categories:: articles, helpers
.. tags:: text,summary,excerpt,Helpers

