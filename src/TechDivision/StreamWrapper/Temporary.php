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

/**
 * This is wrapper for a stream that uses a static variable as storage.
 *
 * @package TechDivision_StreamWrapper
 * @author Tim Wagner <t.wagner@techdivision.com>
 * @copyright TechDivision GmbH
 * @link http://www.techdivision.com
 * @license GPL
 */
class TechDivision_StreamWrapper_Temporary
{

    /**
     * The internal array for storing the stream's value.
     * @var array
     */
    private $_vars = array();

	/**
	 * Holds the current read/write position of the stream.
	 * @var integer
	 */
    private $_position;

    /**
     * The instance.
     * @var TechDivision_StreamWrapper_Temporary
     */
    private static $_self = null;

    /**
     * Sets the stream's value.
     * @param $name The variable name for storing the passed value
     * @param $value The value to store
     * @return void
     */
    public function setValue($name, $value = null)
    {
        $this->_vars[$name] = $value;
    }

    /**
     * Returns the requested stream's value.
     * @param $name The stream's name to return the value for
     * @return string The value
     */
    public function getValue($name)
    {
        if (!array_key_exists($name, $this->_vars)) {
            return false;
        }
        return $this->_vars[$name];
    }

    /**
     * Returns the instance as singleton.
     *
     * @return TechDivision_StreamWrapper_Temporary The instance
     */
    public static function create()
    {
        if (TechDivision_StreamWrapper_Temporary::$_self == null) {
            TechDivision_StreamWrapper_Temporary::$_self =
                new TechDivision_StreamWrapper_Temporary();
        }

        return TechDivision_StreamWrapper_Temporary::$_self;
    }

    /**
	 * This method is called immediately after your stream object is
	 * created. path specifies the URL that was passed to fopen() and that
	 * this object is expected to retrieve. You can use parse_url() to
	 * break it apart.
	 *
	 * @param string $path
	 * @param string $mode
	 * @param integer $options
	 * @param string $opened_path
	 * @return boolean
	 */
    function stream_open($path, $mode, $options, $opened_path)
    {
    	// try to parse the passed path
    	if (($url = parse_url($path)) === false) {
    	    // if an error occurs, return FALSE
    		return false;
    	}
    	// initialize the scheme
    	$scheme = $url["scheme"];
    	if (!empty($scheme)) {
    	    // if no scheme was found, use the default one
    		$this->_scheme = $scheme;
    	}
    	// initialize the members
        $this->_varname = substr(
            $path, strlen(
                $this->_scheme .
                PATH_SEPARATOR .
                DIRECTORY_SEPARATOR .
                DIRECTORY_SEPARATOR
            )
        );
        $this->_position = 0;
        return true;
    }

    /**
	 * This method is called in response to fread() and fgets() calls on the
	 * stream. You must return up-to count bytes of data from the current
	 * read/write position as a string. If there are less than count  bytes
	 * available, return as many as are available.
	 *
	 * If no more data is available, return either FALSE or an empty string.
	 * You must also update the read/write position of the stream by the
	 * number of bytes that were successfully read.
	 *
	 * @param integer $count
	 * @return string
	 */
    function stream_read($count)
    {
        // load the instance
        $val = TechDivision_StreamWrapper_Temporary::create()->getValue(
	        $this->_varname
	    );
        // check if a value is found
	    if ($val === false) {
	        // if not, return false
	        return false;
	    }
        // return the value
    	$ret = substr(
    	    $val,
    	    $this->_position,
    	    $count
    	);
        // set the position
        $this->_position += strlen($ret);
        return $ret;
    }

    /**
	 * This method is called in response to fwrite() calls on the stream.
	 * You should store data into the underlying storage used by your
	 * stream. If there is not enough room, try to store as many bytes as
	 * possible. You should return the number of bytes that were
	 * successfully stored in the stream, or 0 if none could be stored.
	 *
	 * You must also update the read/write position of the stream by the
	 * number of bytes that were successfully written.
	 *
	 * @param string $data
	 * @return integer
	 */
    function stream_write($data)
    {
		$left = substr(
		    TechDivision_StreamWrapper_Temporary::create()->getValue(
		        $this->_varname
		    ),
		    0,
		    $this->_position
		);
    	$right = substr(
    	    TechDivision_StreamWrapper_Temporary::create()->getValue(
    	        $this->_varname
    	    ),
    	    $this->_position + strlen($data)
    	);
    	TechDivision_StreamWrapper_Temporary::create()->setValue(
    	    $this->_varname,
    	    $left . $data . $right
    	);
        return strlen($data);
    }

    /**
	 * This method is called in response to ftell() calls on the stream.
	 * You should return the current read/write position of the stream.
	 *
	 * @return integer
	 */
    function stream_tell()
    {
        return $this->_position;
    }

    /**
	 * This method is called in response to feof()  calls on the stream.
	 * You should return TRUE if the read/write position is at the end
	 * of the stream and if no more data is available to be read, or
	 * FALSE otherwise.
	 *
	 * @return boolean
	 */
    function stream_eof()
    {
        return $this->_position >= strlen(
            TechDivision_StreamWrapper_Temporary::create()->getValue(
                $this->_varname
            )
        );
    }

    /**
	 * This method is called in response to fstat()  calls on the stream
	 * and should return an array containing the same values as appropriate
	 * for the stream.
	 *
	 * @return array
	 */
    function stream_stat()
    {
  		return array(
  			'dev'   => 0,
			'ino'   => 0,
			'mode'  => 0,
			'nlink' => 0,
			'uid'   => 0,
			'gid'   => 0,
			'rdev'  => 0,
			'size'  => strlen(
  		        TechDivision_StreamWrapper_Temporary::create()->getValue(
  		            $this->_varname
  		        )
  		    ),
			'atime' => 0,
			'mtime' => 0,
			'ctime' => 0,
			'blksize' => 4096,
			'blocks'  => 1
  		);
    }

    /**
     * This method is called in response to stat() calls on the URL paths
     * associated with the wrapper and should return as many elements in
     * common with the system function as possible. Unknown or unavailable
     * values should be set to a rational value (usually 0).
     *
     * @param string $path
     * @param integer $flags
     * @return boolean
     */
    function url_stat($path, $flags)
    {
        if (!$this->stream_open($path, '', '', '')) {
        	return false;
        }
        // initialize the values
        $inode = 0;
        $device = 0;
        $size = 0;
        $atime = 0;
        $mtime = 0;
        $ctime = 0;
        // initialize the values
        $keys = array(
            'dev'     => $device,
            'ino'     => $inode,
            'mode'    => 33216,
            'nlink'   => 0,
            'uid'     => 0,
            'gid'     => 0,
            'rdev'    => 0,
            'size'    => strlen(
                TechDivision_StreamWrapper_Temporary::create()->getValue(
                    $this->_varname
                )
            ),
            'atime'   => $atime,
            'mtime'   => $mtime,
            'ctime'   => $ctime,
            'blksize' => 0,
            'blocks'  => 0
        );
        // return the information
        return $keys;
    }

    /**
     * This method is called in response to fseek()  calls on the stream.
     * You should update the read/write position of the stream according
     * to offset and whence. See fseek()  for more information about these
     * parameters. Return TRUE if the position was updated, FALSE otherwise.
     *
     * @param integer $offset
     * @param integer $whence
     * @return boolean
     */
    function stream_seek($offset, $whence)
    {
        switch($whence) {
            case SEEK_SET:
                // get the string's length
                $length = strlen(
                    TechDivision_StreamWrapper_Temporary::create()->getValue(
                        $this->_varname
                    )
                );
            	if ($offset < $length && $offset >= 0) {
                     $this->_position = $offset;
                     return true;
                } else {
                     return false;
                }
                break;
            case SEEK_CUR:
                if($offset >= 0) {
                     $this->_position += $offset;
                     return true;
                } else {
                     return false;
                }
                break;
            case SEEK_END:
                $length = strlen(
                    TechDivision_StreamWrapper_Temporary::create()->getValue(
                        $this->_varname
                    )
                );
                if($length + $offset >= 0) {
                     $this->_position = strlen(
                         TechDivision_StreamWrapper_Temporary::create()
                             ->getValue($this->_varname)
                     ) + $offset;
                     return true;
                } else {
                     return false;
                }
                break;

            default:
                return false;
        }
    }
}