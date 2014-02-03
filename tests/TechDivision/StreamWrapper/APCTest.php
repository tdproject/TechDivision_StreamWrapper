<?php

/**
 * License: GNU General Public License
 *
 * Copyright (c) 2009 TechDivision GmbH.  All rights reserved.
 * Note: Original work copyright to respective authors
 *
 * This file is part of TechDivision GmbH - Connect.
 *
 * faett.net is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * faett.net is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307,
 * USA.
 *
 * @package TechDivision_StreamWrapper
 */

require_once "TechDivision/StreamWrapper/APC.php";

/**
 * This class implements the test cases
 * of the APC based stream wrapper.
 *
 * @package TechDivision_StreamWrapper
 * @author Tim Wagner <t.wagner@techdivision.com>
 * @copyright TechDivision GmbH
 * @link http://www.techdivision.com
 * @license GPL
 */
class TechDivision_StreamWrapper_APCTest
    extends PHPUnit_Framework_TestCase {

    /**
     * Initializes the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        // try to register the temporary stream wrapper
        $this->assertTrue(
            stream_wrapper_register(
            	'apc',
            	'TechDivision_StreamWrapper_APC'
            )
        );
    }

    /**
     * Resets the test environment.
     *
     * @return void
     */
    public function tearDown()
    {
        stream_wrapper_unregister('apc');
    }

	/**
	 * This method tests the registration and tries to load
	 * a class from the stream.
	 *
	 * @return void
	 */
	public function testStreamWrite()
	{
	    // open the file to write to the stream
	    $toWrite = file_get_contents(
	    	'TechDivision/StreamWrapper/TestClassAPC.php',
	        true
	    );
	    // initialize the stream URL
	    $stream = 'apc://TechDivision/StreamWrapper/TestClassAPC.php';
        // write the file contents to the stream
	    $this->assertEquals(
	        strlen($toWrite),
	        file_put_contents($stream, $toWrite)
	    );
        // check that the content is available in the stream
	    $this->assertEquals($toWrite, file_get_contents($stream));
        // include the content from the stream
	    require_once $stream;
        // check the the conctent was included correctly
	    $test = new TechDivision_StreamWrapper_TestClassAPC($name = 'Foo');
	    $this->assertEquals($name, $test->getName());
    }
}