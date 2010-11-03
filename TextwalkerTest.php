<?php
require_once 'simpletest/unit_tester.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/mock_objects.php';
require_once 'simpletest/autorun.php';
require_once 'Textwalker.php';
error_reporting(E_ALL);

define('DOCUMENT', 'Lorem ipsum dolor sit ipsum amet, consectetuer adipiscing elit.'); 

abstract class CursorTestCase extends UnitTestCase {
    function setUp() {
        $this->object = $this->createTestObject();
    }
    function testNextFindsText() {
        $text = $this->object->nextMatch('/i...m/');
        $this->assertEqual('ipsum',$text);
    }

    function testNextMatchReturnsFalseOnNonExistentText() {
        $rv = $this->object->nextMatch('/Mood Indigo/');
        $this->assertFalse($rv);
    }

    function testNextMatchAfterGotoNextFindsSecondText() {
        $this->object->gotoNext('/i...m/');
        $text = $this->object->nextMatch('/s\w\w/');
        $this->assertEqual('sit',$text);
    }
}

class CursorTest extends CursorTestCase {

    function createTestObject() {
        return new Textwalker_Cursor(DOCUMENT);
    }

    function testNextFindsCursor() {
        $cursor = $this->object->next('/i...m/');
        $this->assertEqual(6,$cursor->getStartPosition());
        $this->assertEqual(10,$cursor->getEndPosition());
    }

    function testCharRightMakesNonzeroCursorZero() {
        $this->object->charRight(1);
        $this->object->setEndPosition(5);
        $this->object->charRight();
        $this->assertEqual(6,$this->object->getStartPosition());
        $this->assertEqual(6,$this->object->getEndPosition());
    }

    function testCharLeftMakesNonzeroCursorZero() {
        $this->object->charRight(5);
        $this->object->setEndPosition(10);
        $this->object->charLeft();
        $this->assertEqual(4,$this->object->getStartPosition());
        $this->assertEqual(4,$this->object->getEndPosition());
    }

    function testCharRightMovesZeroLengthCursor() {
        $this->object->charRight(5);
        $this->assertEqual(5,$this->object->getStartPosition());
        $this->assertEqual(5,$this->object->getEndPosition());
    }

    function testCharLeftMovesZeroLengthCursor() {
        $this->object->charRight(5);
        $this->object->charLeft(2);
        $this->assertEqual(3,$this->object->getStartPosition());
        $this->assertEqual(3,$this->object->getEndPosition());
    }

    function testNextDoesNotMoveCursor() {
        $text = $this->object->nextMatch('/i...m/');
        $this->assertEqual(0,$this->object->getStartPosition());
        $this->assertEqual(0,$this->object->getEndPosition());
    }

    function testGotoNextMovesCursorStartPosition() {
        $this->object->gotoNext('/i...m/');
        $this->assertEqual(6,$this->object->getStartPosition());
    }

    function testGotoNextMovesCursorEndPosition() {
        $this->object->gotoNext('/i...m/');
        $this->assertEqual(10,$this->object->getEndPosition());
    }

    function testGotoNextTwiceGoesToSecondTextPosition() {
        $this->object->gotoNext('/i...m/');
        $this->object->gotoNext('/i...m/');
        $this->assertEqual(22,$this->object->getStartPosition());
        $this->assertEqual(26,$this->object->getEndPosition());
    }

    function testGetTextReturnsCurrentText() {
        $this->object->gotoNext('/i...m/');
        $this->assertEqual('ipsum',$this->object->getText());
    }

    function testCursorCanBeCloned() {
        $this->object->gotoNext('/i...m/');
        $clone = clone $this->object;
        $clone->gotoNext('/i...m/');
        $this->assertEqual(6,$this->object->getStartPosition());
        $this->assertEqual(22,$clone->getStartPosition());
    }

}

class TextwalkerDelegatesToCursorTest extends CursorTestCase {
    function createTestObject() {
        return new Textwalker(DOCUMENT);
    }
}

class CursorPositionTest extends UnitTestCase {
    function setUp() {
        $this->position = new Textwalker_CursorPosition(2,4);
    }

    function testMoveToMovesBothStartAndEnd() {
        $this->position->moveTo(10);
        $this->assertEqual(3,$this->position->getLength());
        $this->assertEqual(10,$this->position->getStart());
        $this->assertEqual(12,$this->position->getEnd());
    }

    function testMoveForwardMovesBothStartAndEnd() {
        $this->position->move(2);
        $this->assertEqual(3,$this->position->getLength());
        $this->assertEqual(4,$this->position->getStart());
        $this->assertEqual(6,$this->position->getEnd());
    }

    function testMoveBackwarddMovesBothStartAndEnd() {
        $this->position->move(-2);
        $this->assertEqual(3,$this->position->getLength());
        $this->assertEqual(0,$this->position->getStart());
        $this->assertEqual(2,$this->position->getEnd());
    }

    function testMoveAfter() {
        $this->position->moveAfterSelf();
        $this->assertEqual(3,$this->position->getLength());
        $this->assertEqual(5,$this->position->getStart());
        $this->assertEqual(7,$this->position->getEnd());
    }

    function testGetLength() {
        $this->assertEqual(3,$this->position->getLength());
    }

    function testSetLengthMovesEnd() {
        $this->position->setLength(5);
        $this->assertEqual(6,$this->position->getEnd());
    }

    function testSlice() {
        $string = $this->position->sliceText('Turkey');
        $this->assertEqual('rke',$string);
    }
}

class TextwalkerTest extends UnitTestCase {
    function setUp() {
        $this->walker = new Textwalker(DOCUMENT);
    }

    function testGetTextRetrievesWholeText() {
        $this->assertEqual(DOCUMENT,$this->walker->getText());
    }

    function testBookmarkSetAtCursorPositionCanBeRetrievedByName() {
        $this->walker->gotoNext('/i...m/');
        $this->walker->bookmark('Ipsum');
        $this->walker->gotoNext('/amet/');
        $this->assertEqual(
            'ipsum',
            $this->walker->getBookmark('Ipsum')->getText()
        );
    }

    function testGotoNextPlusBookmarkIsFluent() {
        $this->walker->gotoNext('/i...m/')->bookmark('Ipsum');
        $this->assertEqual(
            'ipsum',
            $this->walker->getBookmark('Ipsum')->getText()
        );
    }

    function testBetweenReturnsTextBetweenBookmarks() {
        $this->walker->gotoNext('/i...m/')->bookmark('one');
        $this->walker->gotoNext('/i...m/')->bookmark('two');
        $this->assertEqual(
            ' dolor sit ',
            $this->walker->between('one','two')->getText()
        );
    }
}

