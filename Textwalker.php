<?php
/**
* Object to represent the position of the cursor in the text
*
* The cursor represents the 
*/
class Textwalker_CursorPosition {
    private $start;
    private $end;

    /**
    * @param int $start Position of start of cursor
    * @param int $end Position of end of cursor
    */
    public function __construct($start=0,$end=0) {
        $this->start = $start;
        $this->end = $end;
    }

    /**
    * Sets the length of the cursor, moving the end accordingly
    *
    * @param int $length New cursor length
    */

    function setLength($length) {
        $this->end = $this->start + $length - 1;
    }

    /**
    * Gets the length of the cursor
    *
    * @return int $length Cursor length
    */
    function getLength() {
        return $this->end - $this->start + 1;
    }
    function getStart() { return $this->start; }
    function setStart($arg) { $this->start = $arg; }
    function getEnd() { return $this->end; }
    function setEnd($arg) { $this->end = $arg; }

    /**
    * Extracts a piece of the given text at the cursor position
    *
    * $param $text The text to be sliced
    * @return Extracted text
    */
    public function sliceText($text) {
        return substr($text,$this->start,$this->getLength());
    }

    public function move($n) {
        $this->start += $n;
        $this->end += $n;
    }

    public function moveTo($n) {
        $this->move($n - $this->start);
    }

    public function moveAfterSelf() {
        $this->moveTo($this->getEnd() + 1);
    }
}

/**
* Class to represent the cursor in the text
*
* Holds a reference to the text it's the cursor for
*/
class Textwalker_Cursor {
    private $context;
    private $position;

    public function __construct($context,$position=FALSE) {
        $this->context = $context;
        $this->position = $position 
        ? $position 
        : new Textwalker_CursorPosition;
    }
    /**
    * Finds text matching a regex after the current cursor position
    *
    * Can be used to check whether there is a next occurrence of a
    * string without moving the cursor to it
    */
    public function nextMatch($regex) {
        $rv = preg_match(
            $regex,$this->context,$m,NULL,$this->getEndPosition());
        if (!$rv) return FALSE;
        return $m[0];
    }

    public function next($regex) {
        $cursor = clone $this;
        $cursor->gotoNext($regex);
        return $cursor;
    }

    public function gotoNext($regex) {
        $text = $this->nextMatch($regex);
        $start = strpos($this->context,$text,$this->getEndPosition());
        $this->position->setStart($start);
        $this->position->setLength(strlen($text));
        return $text;
    }

    public function charLeft($num=1) {
        $target = $this->position->getStart() - $num;
        $this->position->setEnd($target);
        $this->position->setStart($target);
    }

    public function charRight($num=1) {
        $target = $this->position->getEnd() + $num;
        $this->position->setEnd($target);
        $this->position->setStart($target);
    }

    public function getStartPosition() {
        return $this->position->getStart();
    }

    public function getEndPosition() {
        return $this->position->getEnd();
    }

    public function setEndPosition($n) {
        $this->position->setEnd($n);
    }
    public function getText() {
        return $this->position->sliceText($this->context);
    }

    public function __clone() {
        $this->position = clone $this->position;
    }

}

class Textwalker {
    private $text;
    private $bookmarks = array();

    public function __construct($text) {
        $this->text = $text;
        $this->cursor = new Textwalker_Cursor($text);
    }

    public function nextMatch($regex) {
        return $this->cursor->nextMatch($regex);
    }

    public function gotoNext($regex) {
        $this->cursor->gotoNext($regex);
        return $this;
    }

    public function getText() {
        return $this->text;
    }

    public function bookmark($name) {
        $this->bookmarks[$name] = clone $this->cursor;

    }

    public function getBookmark($name) {
        return $this->bookmarks[$name];
    }

    public function between($bm1,$bm2) {
        $pos = new Textwalker_CursorPosition(
            $this->bookmarks[$bm1]->getEndPosition() + 1,
            $this->bookmarks[$bm2]->getStartPosition() -1
        );
        return new Textwalker_Cursor($this->text,$pos);
    }
    public function getStartPosition() {
        return $this->cursor->getStartPosition();
    }

    public function getEndPosition() {
        return $this->cursor->getEndPosition();
    }


   
}
