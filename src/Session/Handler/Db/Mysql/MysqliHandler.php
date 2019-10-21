<?php
/**
 * Session handler in Db MySQL, MariaDB with use mysqli driver
 * 
 * http://php.net/manual/ru/function.session-set-save-handler.php
 * http://php.net/manual/ru/class.sessionhandlerinterface.php
 *
 * Столбец write_count, дополнительно, служит для того, чтобы в функции write() запрос update всегда возвращал 1, если сессия существует.
 * Иначе, если запустить UPDATE из write() 2 и более раз в течение секунды с данными на обновление идентичными в БД mysqli_affected_rows() вернет 0,
 * вследствие чего запустится запрос на INSERT, который вернет ошибку, так как session_key существует.
 * 
 * The nowDateTime property, used instead of the Mysql NOW() operator, to eliminate 
 * the problems of the difference between the time zones of PHP and the Mysql Database
 */

declare(strict_types = 1);

namespace Vgip\Gip\Session\Handler\Db\Mysql;

use mysqli;
use mysqli_result;
use DateTime;
use DateTimeZone;
use Vgip\Gip\Exception\DomainException;
use Vgip\Gip\Session\Serialize;
use Vgip\Gip\Session\Config AS SessionConfig;
use Vgip\Gip\Session\Handler AS HandlerMain;

class MysqliHandler extends HandlerMain implements \SessionHandlerInterface
{
    /**
     * Db connection driver \mysqli with configured connection
     * 
     * @var object 
     */
    private $mysqli;
    
    /**
     * Session table name
     * 
     * @var string 
     */
    private $table;
    
    /**
     * Main session configuration
     * 
     * @var array
     */
    private $config;
    
    /**
     * Internal timezone
     * 
     * @var string 
     */
    private $timezoneInternal;
    
    private $timezoneDb;

    private $formatDateTime = 'Y-m-d H:i:s';
    
    /**
     * Date and time of session creation
     * 
     * @var object 
     */
    private $dateCreateObj = null;
    
    /**
     * Date and time in MySQL format
     * 
     * @var string 
     */
    private $nowDateTime;
    
    private $savePath;
    
    private $sessionName;
    
    private $parentId = null;
    
    private $isValidSessionId            = false;
    private $isValidSessionIdRead        = false;
    private $regenerateSessionIdProcess  = false;
    

    public function __construct(mysqli $mysqli, SessionConfig $config, string $sessionTablePrefix = '', string $sessionTableName = 'session', string $timezoneInternal, string $timezoneDb)
    {
        $this->mysqli       = $mysqli;
        $this->config       = $config->getAll();
        $this->table        = $sessionTablePrefix.$sessionTableName;
        
        $this->timezoneInternal = $timezoneInternal;
        $this->timezoneDb       = $timezoneDb;
        
        $nowDateTime = new DateTime('now', new DateTimeZone($timezoneDb));
        $this->nowDateTime = $nowDateTime->format($this->formatDateTime);
        $this->dateCreateObj = new DateTime('now', new DateTimeZone($timezoneDb));
    }

    public function open($savePath, $sessionName) : bool
    {
        $this->savePath    = $savePath;
        $this->sessionName = $sessionName;

        return true;
    }

    public function close() : bool
    {
        return true;
    }

    public function read($id): string
    {
        $res = '';
        
        if (true === $this->regenerateSessionIdProcess) {
            $this->isValidSessionIdRead = true;
            return '';
        }
        
        if (true === $this->isValidSessionId($id, $this->config['sid_length'], 0)) {
            $this->isValidSessionId = true;
            $query = '
                SELECT id, valid, data, date_create
                FROM `'.$this->table.'`
                WHERE session_id = "'.$this->mysqli->escape_string($id).'"';
            $dataFromDb = $this->getAllFromDb($query);
            if (count($dataFromDb) === 1) {
                $dateCreate = new DateTime($dataFromDb[0]['date_create'], new DateTimeZone($this->timezoneDb));
                if ((time() - $dateCreate->getTimestamp()) <= $this->config['lifetime']) {
                    if (true === (bool)$dataFromDb[0]['valid']) {
                        $this->dateCreateObj = $dateCreate;
                        $res = (string)$dataFromDb[0]['data'];
                        $this->parentId = (int)$dataFromDb[0]['id'];
                        $this->isValidSessionIdRead = true;
                    } else {
                        $this->parentId = (int)$dataFromDb[0]['id'];
                    }
                }
            } else if (null === filter_input(INPUT_COOKIE, $this->config['name'])) {
                $this->isValidSessionIdRead = true;
            } 
        }
        
        return $res;
    }

    public function write($id, $data)
    {
        if (false === $this->isValidSessionIdRead) {
            /** 
             * @todo Save security warning to log 
             * Message: Attempt to use incorrect session identifier
             */
            
            $this->regenerateSessionIdProcess = true;
            
            return true;
        }

        $dataArray = Serialize::decode($data, $this->config['serialize_handler']);
        $userIdSet = (isset($dataArray['user_id'])) ? (int)$dataArray['user_id'] : 'NULL';
        
        $valid = 1;
        if ((time() - $this->dateCreateObj->getTimestamp()) > $this->config['regenerate_session_id_time']) {
            $valid = 0;
        }
        
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
        $userAgentSet = (null === $userAgent) ? 'NULL' : '"'.md5($this->mysqli->escape_string($userAgent)).'"';
        
        $remoteIpSet = isset($_SERVER['REMOTE_ADDR']) ? '"'.$this->mysqli->escape_string(inet_pton($_SERVER['REMOTE_ADDR'])).'"' : 'NULL';
        
        $parentIdSet   = (null === $this->parentId) ? 'NULL' : $this->parentId;
        
        $query = 
           'UPDATE `'.$this->table.'`
            SET valid           = "'.$valid.'",
                user_id         = '.$userIdSet.',
                data            = "'.$this->mysqli->escape_string($data).'", 
                update_counter  = update_counter + 1,
                date_update     = "'.$this->mysqli->escape_string($this->nowDateTime).'"
            WHERE session_id    = "'.$this->mysqli->escape_string($id).'"';
        $this->dbQuery($query);
        
        if ($this->mysqli->affected_rows === 0) {
            $query = '
                INSERT INTO `'.$this->table.'`
                SET id              = DEFAULT,
                    parent_id       = '.$parentIdSet.',
                    valid           = "1",
                    session_id      = "'.$this->mysqli->escape_string($id).'",
                    user_id         = '.$userIdSet.',
                    data            = "'.$this->mysqli->escape_string($data).'",
                    ip              = '.$remoteIpSet.', 
                    user_agent      = '.$userAgentSet.', 
                    update_counter  = 1, 
                    date_create     = "'.$this->mysqli->escape_string($this->nowDateTime).'", 
                    date_update     = "'.$this->mysqli->escape_string($this->nowDateTime).'"';
            $this->dbQuery($query);
            
            $this->dateCreateObj = new DateTime('now', new DateTimeZone($this->timezoneDb));
        }
        
        return true;
    }

    public function destroy($id)
    {
        $query = '
            DELETE `'.$this->table.'`
            FROM `'.$this->table.'`
            WHERE session_id = "'.$this->mysqli->escape_string($id).'"';
        $this->dbQuery($query);
        $result = true;
        
        return $result;
    }

    /**
     * Set this for test - 100% run gc()
     * ini_set('session.gc_probability',   100);
     * ini_set('session.gc_divisor',       100);
     * 
     * @param type $maxlifetime
     * @return boolean
     */
    public function gc($maxlifetime)
    {
        $query = '
            DELETE `'.$this->table.'`
            FROM `'.$this->table.'`
            WHERE date_update < DATE_SUB("'.$this->mysqli->escape_string($this->nowDateTime).'", INTERVAL '.(int)$maxlifetime.' SECOND)'; 
        $this->dbQuery($query);
        
        $query = '
            UPDATE `'.$this->table.'`
            SET valid = "0"
            WHERE date_update < DATE_SUB("'.$this->mysqli->escape_string($this->nowDateTime).'", INTERVAL '.$this->config['lifetime'].' SECOND) AND
                  valid = "1"';
        $this->dbQuery($query);

        return true;
    }
    
    /**
     * 
     * @return string - date create session timestamp
     */
    public function getDateCreate()
    {
        $returnDatetime = clone $this->dateCreateObj;
        $returnDatetime->setTimezone(new DateTimeZone($this->timezoneInternal));
        
        return $returnDatetime;
    }
    
    public function isValidSessionIdRead()
    {
        return $this->isValidSessionIdRead;
    }

    private function dbQuery(string $query)
    {
        //echo $query."\n";
        $result = $this->mysqli->query($query);

        if (!$result) {
            throw new DomainException($this->mysqli->error);
        }

        return $result;
    }
    
    private function getAllFromDb($query) : array
    {
        $res = [];
        
        $result = $this->dbQuery($query);
        if (!($result instanceof mysqli_result)) {
            throw new DomainException('Result is not instance if mysqli_result, this query is not supported');
        }
        
        while ($row = $result->fetch_assoc()) {
            $res[] = $row;
        }
        $result->free();
        
        return $res;
    }
}
