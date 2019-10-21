<?php
/**
 * Basic framwork storage
 */

declare(strict_types = 1);

namespace Vgip\Gip\Storage;

use Vgip\Gip\Common\Singleton;

class Storage
{
    use Singleton;
    
    /** Basic site configuration */
    private $basicConfig     = null;
    
    /** Basic DB configuration */
    private $dbConfig        = null;
    
    /** Db object */
    private $db              = null;
    
    
    public function setBasicConfig($basicConfig) 
    {
        $this->basicConfig = $basicConfig;
    }

    public function getBasicConfig() 
    {
        return $this->basicConfig;
    }
    
    public function setDbConfig($dbConfig) 
    {
        $this->dbConfig = $dbConfig;
    }

    public function getDbConfig() 
    {
        return $this->dbConfig;
    }

    public function setDb($db) 
    {
        $this->db = $db;
    }

    public function getDb() 
    {
        return $this->db;
    }

}
