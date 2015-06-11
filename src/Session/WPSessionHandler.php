<?php 

namespace Tiga\Framework\Session;

use Tiga\Framework\Facade\DatabaseFacade as DB;
use Tiga\Framework\Exception\Exception as Exception;

class WPSessionHandler implements \SessionHandlerInterface {

	var $table;
	var $idCol;
	var $dataCol;
	var $lifetimeCol;
	var $timeCol;

    /**
     * @var bool Whether gc() has been called
     */
    private $gcCalled = false;

    /**
     * @var bool True when the current session exists but expired according to session.gc_maxlifetime
     */
    private $sessionExpired = false;


	function __construct($db) {

        global $wpdb;

        $this->db = $db;

        $this->table = $wpdb->prefix."tiga_session";
        $this->idCol = "sess_id";
        $this->dataCol = "sess_data";
        $this->timeCol = "sess_time";
        $this->lifetimeCol = "sess_lifetime";

        // Create session table
        $this->initTable();
  
	}

    function initTable() {

        // If table not exist ,create the table
        $tableExist = DB::query("SHOW TABLES LIKE '{$this->table}'")->execute();

        if(!$tableExist) {

            $sql = "CREATE TABLE `$this->table` (
                            `{$this->idCol}` VARBINARY(128) NOT NULL PRIMARY KEY,
                            `{$this->dataCol}` BLOB NOT NULL,
                            `{$this->lifetimeCol}` MEDIUMINT NOT NULL,
                            `{$this->timeCol}` INTEGER UNSIGNED NOT NULL
                            ) COLLATE utf8_bin, ENGINE = InnoDB;";

            $result = DB::query($sql)->execute();

            $tableExist = DB::query("SHOW TABLES LIKE '{$this->table}'")->execute();

            // Check table creation result, if not throw error
            if(!$tableExist)
                throw new Exception('Fail to create database session table');

        }

    }

	/**
     * Re-initializes existing session, or creates a new one.
     *
     * @see http://php.net/sessionhandlerinterface.open
     *
     * @param string $savePath    Save path
     * @param string $sessionName Session name, see http://php.net/function.session-name.php
     *
     * @return bool true on success, false on failure
     */
    public function open($savePath, $sessionName) {

    	// LF Always boot after WP, which means database is ready to use. If not, WP can't be started
    	return true;
    }

    /**
     * Closes the current session.
     *
     * @see http://php.net/sessionhandlerinterface.close
     *
     * @return bool true on success, false on failure
     */
    public function close() {

        if ($this->gcCalled) {

            $this->gcCalled = false;

            // delete the session records that have expired
            $sql = "DELETE FROM $this->table WHERE $this->lifetimeCol + $this->timeCol < :time";

            $stmt = DB::query($sql)
                        ->bindValue(':time', time())
                        ->execute();

        }

        return true;

    }

    /**
     * Reads the session data.
     *
     * @see http://php.net/sessionhandlerinterface.read
     *
     * @param string $sessionId Session ID, see http://php.net/function.session-id
     *
     * @return string Same session data as passed in write() or empty string when non-existent or on failure
     */
    public function read($sessionId) {


        $this->sessionExpired = false;

        $selectSql = $this->getSelectSql();
        $selectStmt = DB::query($selectSql)
                        ->bind(':id', $sessionId);
        
        $sessionRows = $selectStmt->row();


        if ($sessionRows) {


            if ($sessionRows->{$this->timeCol} + $sessionRows->{$this->lifetimeCol} < time()) {

                $this->sessionExpired = true;

                return '';
            }


            return is_resource($sessionRows->{$this->dataCol}) ? stream_get_contents($sessionRows->{$this->dataCol}) : $sessionRows->{$this->dataCol};
        }

        return '';

    }

    /**
     * Writes the session data to the storage.
     *
     * Care, the session ID passed to write() can be different from the one previously
     * received in read() when the session ID changed due to session_regenerate_id().
     *
     * @see http://php.net/sessionhandlerinterface.write
     *
     * @param string $sessionId Session ID , see http://php.net/function.session-id
     * @param string $data      Serialized session data to save
     *
     * @return bool true on success, false on failure
     */
    public function write($sessionId, $data) {

        $maxlifetime = (int) ini_get('session.gc_maxlifetime');

        $mergeSql = $this->getMergeSQL();

        $result = DB::query($mergeSql)
            ->bind(':id', $sessionId)
            ->bind(':data', $data)
            ->bind(':lifetime',$maxlifetime)
            ->bind(':time',time())
            ->execute();


        if($result!==false)
            return true;

        // Session Save faile
        throw new Exception('Fail to save session to database');
 
    }

    /**
     * Destroys a session.
     *
     * @see http://php.net/sessionhandlerinterface.destroy
     *
     * @param string $sessionId Session ID, see http://php.net/function.session-id
     *
     * @return bool true on success, false on failure
     */
    public function destroy($sessionId) {

        // delete the record associated with this id
        $sql = "DELETE FROM $this->table WHERE $this->idCol = :id";

        
        $stmt = DB::query($sql)
                    ->bind(':id', $sessionId)
                    ->execute();
        
        if($stmt===false) 
            throw new Exception('Fail destroy session');

        return true;
    
    }

    /**
     * Cleans up expired sessions (garbage collection).
     *
     * @see http://php.net/sessionhandlerinterface.gc
     *
     * @param string|int $maxlifetime Sessions that have not updated for the last maxlifetime seconds will be removed
     *
     * @return bool true on success, false on failure
     */
    public function gc($maxlifetime) {
        $this->gcCalled = true;

        return true;
    }

    private function getMergeSQL() {

       return "INSERT INTO $this->table ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol) 
               VALUES (:id, :data, :lifetime, :time) ".
               "ON DUPLICATE KEY UPDATE $this->dataCol = VALUES($this->dataCol), $this->lifetimeCol = VALUES($this->lifetimeCol), $this->timeCol = VALUES($this->timeCol)";
       
    }

    private function getSelectSQL() {
        
        return "SELECT $this->dataCol, $this->lifetimeCol, $this->timeCol FROM $this->table WHERE $this->idCol = :id";
    
    }

}