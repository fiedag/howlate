<?php

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-10-19 at 10:09:52.
 */
class LoggingTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Logging
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }

    /**
     * @covers Logging::trlog
     * @todo   Implement testTrlog().
     */
    public function testTrlog() {
        // Remove the following lines when you implement this test.
        $message = trim(uniqid('Unit Test'));
        Logging::trlog(TranType::MISC_MISC,$message);

        $rows = Logging::getlog($message);
        $this->assertEquals($rows[0]->Details,$message);
    }



}
