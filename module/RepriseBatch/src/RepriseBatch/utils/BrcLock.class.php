<?php
/**
 *
 * @package cgp-web
 * @copyright Copyright (C) 2011 BROCELIA. All rights reserved.
 */

abstract class BrcLock {
    abstract public function isLocked();
    static $locks = array();
    private static function lock($scriptname, $type='file' , $set = true) {

        /*if($type!='memcache' || !KeyValueConnection::getConnection()) {
            $type = 'file';
        }*/
        if(isset(self::$locks[$type][$scriptname])) return false;
        if($type=='memcache') {
            $lock = new BrcLockMemcache($scriptname,$set);
        } else {
            $lock = new BrcLockFile($scriptname,$set);
        }
        if ($set){
            self::$locks[$type][$scriptname] = $lock;
        }
        return $lock->isLocked();
    }

    public static function check($scriptname, $type='file'){
        return self::lock($scriptname, $type , false);
    }
    public static function get($scriptname, $type='file'){
        return self::lock($scriptname, $type , true);
    }
}

class BrcLockFile extends BrcLock {
    private $handle = false;
    private $locked = false;
    private $lockfilename = "";

    public function __construct($scriptname,$set = true) {
        $scriptname = str_replace('|','_',$scriptname);
        $path = dirname($scriptname);
        @mkdir(TMP_DIR."lock", 0777, true);
        $this->lockfilename = TMP_DIR.'lock'.DS.$scriptname.'.lock';
        
        // generons une erreur dans le cas ou le fichier existe deja
        /*if(file_exists($this->lockfilename)) {
            throw new Exception("File lock already exists");            
        }*/

        $this->handle = fopen($this->lockfilename, 'w');
        if($this->handle) {
            $this->locked = flock($this->handle, LOCK_EX | LOCK_NB);
            if(!$this->locked || !$set) {
                fclose($this->handle);
                $this->handle = false;
            }
        }
    }
    function __destruct(){
        if($this->locked && $this->handle) {
            flock($this->handle,LOCK_UN);
            fclose($this->handle);
            // unlink($this->lockfilename);
        }
    }
    public function isLocked() {
        return $this->locked;
    }
}

class BrcLockMemcache extends BrcLock {
    private $key;
    private $locked;
    private $set;
    public function __construct($key, $set=true) {
        $this->key = $key;
        $this->set = $set;
        $cx = KeyValueConnection::getConnection();
        if(!$cx) {
            //trow new Exception ?
            $this->locked = false;
        } elseif($this->set){
            $this->locked = $cx->add('brclock|'.$this->key, '1');
        } else {
            $this->locked = !$cx->get('brclock|'.$this->key);
        }
    }
    function __destruct(){
        if ($this->locked && $this->set ){
            $cx = KeyValueConnection::getConnection();
            if($cx) {
                $cx->delete('brclock|'.$this->key);
            }
        }
    }
    public function isLocked() {
        return $this->locked;
    }
}

/** exemple d'utilisation :

if ( BrcLock::get('test'))
{
    echo 'some work';
    sleep(10);
}
else
{
    echo 'dont work';
}
**/
