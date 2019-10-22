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

use Vgip\Gip\Db\Mysql\MysqlPlaceholder;
use DateTime;
use DateTimeZone;
use Vgip\Gip\Session\Serialize;
use Vgip\Gip\Session\Config AS SessionConfig;
use Vgip\Gip\Session\Handler AS HandlerMain;

class Placeholder extends HandlerMain implements \SessionHandlerInterface
{
    /**
     * Db object
     * 
     * @var object 
     */
    private $db;
    
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
    

    public function __construct(MysqlPlaceholder $db, SessionConfig $config, string $sessionTableName = 'session')
    {
        $this->db           = $db;
        $this->config       = $config->getAll();
        $this->table        = $sessionTableName;
        
        $this->timezoneDb = $db->getTimezone();
        $nowDateTime = new DateTime('now', new DateTimeZone($this->timezoneDb));
        $this->nowDateTime = $nowDateTime->format($this->formatDateTime);
        $this->dateCreateObj = new DateTime('now', new DateTimeZone($this->timezoneDb));
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
                FROM ?t
                WHERE session_id = ?s';
            $dataFromDb = $this->db->getRow($query, $this->table, $id);
            if (count($dataFromDb) > 0) {
                $dateCreate = new DateTime($dataFromDb['date_create'], new DateTimeZone($this->timezoneDb));
                if ((time() - $dateCreate->getTimestamp()) <= $this->config['lifetime']) {
                    if (true === (bool)$dataFromDb['valid']) {
                        $this->dateCreateObj = $dateCreate;
                        $res = (string)$dataFromDb['data'];
                        $this->parentId = (int)$dataFromDb['id'];
                        $this->isValidSessionIdRead = true;
                    } else {
                        $this->parentId = (int)$dataFromDb['id'];
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
        $userIdSet = (isset($dataArray['user_id'])) ? $dataArray['user_id'] : null;
        
        $valid = 1;
        if ((time() - $this->dateCreateObj->getTimestamp()) > $this->config['regenerate_session_id_time']) {
            $valid = 0;
        }
        
        $userAgentSet = isset($_SERVER['HTTP_USER_AGENT']) ? md5($_SERVER['HTTP_USER_AGENT']) : null;
        
        $remoteIpSet = isset($_SERVER['REMOTE_ADDR']) ? inet_pton($_SERVER['REMOTE_ADDR']) : null;
        
        $parentIdSet   = $this->parentId; /** Null or integer value */
        
        $query = 
           'UPDATE ?t
            SET valid           = ?s,
                user_id         = ?i,
                data            = ?s, 
                update_counter  = update_counter + 1,
                date_update     = ?s
            WHERE session_id    = ?s';
        $this->db->query($query, $this->table, $valid, $userIdSet, $data, $this->nowDateTime, $id);
        
        if ($this->db->affectedRows() === 0) {
            $query = '
                INSERT INTO ?t
                SET id              = DEFAULT,
                    parent_id       = ?i,
                    valid           = "1",
                    session_id      = ?s,
                    user_id         = ?i,
                    data            = ?s,
                    ip              = ?s,
                    user_agent      = ?s,
                    update_counter  = 1, 
                    date_create     = ?s, 
                    date_update     = ?s';
            $this->db->query($query, $this->table, $parentIdSet, $id, $userIdSet, $data, $remoteIpSet, $userAgentSet, $this->nowDateTime, $this->nowDateTime);
            
            $this->dateCreateObj = new DateTime('now', new DateTimeZone($this->timezoneDb));
        }
        
        return true;
    }

    public function destroy($id)
    {
        $query = '
            DELETE ?t
            FROM ?t
            WHERE session_id = ?s';
        $this->db->query($query, $this->table, $this->table, $id);
        $result = true;
        
        return $result;
    }

    /**
     * Set this for test - 100% run gc()
     * 
     * ini_set('session.gc_probability',   100);
     * ini_set('session.gc_divisor',       100);
     * 
     * @param type $maxlifetime
     * @return boolean
     */
    public function gc($maxlifetime)
    {
        $query = '
            DELETE ?t
            FROM ?t
            WHERE date_update < DATE_SUB(?s, INTERVAL ?i SECOND)'; 
        $this->db->query($query, $this->table, $this->table, $this->nowDateTime, $maxlifetime);

        $query = '
            UPDATE ?t
            SET valid = "0"
            WHERE date_update < DATE_SUB(?s, INTERVAL ?i SECOND) AND
                  valid = "1"';
        $this->db->query($query, $this->table, $this->nowDateTime, $this->config['lifetime']);

        return true;
    }
    
    /**
     * 
     * @return string - date create session timestamp
     */
    public function getDateCreate()
    {
        $returnDatetime = clone $this->dateCreateObj;
        $returnDatetime->setTimezone(new DateTimeZone($this->config['timezone']));
        
        return $returnDatetime;
    }
    
    public function isValidSessionIdRead()
    {
        return $this->isValidSessionIdRead;
    }
}
