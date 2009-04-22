<?php
class KISS_KDO_SqlCommandIterator implements Iterator,ArrayAccess,Countable{
    private $mSqlCommand;
    private $mResule;
    private $cur = 0;
    private $length = 0;
    private $mResultType;

    public function __construct(KISS_KDO_SqlCommand $pSqlCommand, $pResule, $pResultType) {
        $this->mSqlCommand = $pSqlCommand;
        $this->mResule = $pResule;
        $this->length = $this->mSqlCommand->db_num_rows($this->mResule);
        $this->mResultType = $pResultType;
    }

    public function __destruct() {
        $this->mSqlCommand->db_free_result($this->mResule);
    }

    public function rewind() {
        $this->cur = 0;
    }

    public function length() {
        return $this->length;
    }
    
    public function count() {
        return $this->length;
    }

    public function key() {
        return $this->cur;
    }

    public function current() {
        $this->mSqlCommand->db_data_seek($this->mResule,$this->cur);
        return $this->mSqlCommand->db_fetch_array($this->mResule, $this->mResultType);
    }

    public function next() {
        $this->cur++;
    }

    public function valid() {
        return $this->cur < $this->length;
    }

    function offsetExists($name) {
        return ((int)$name >= 0 && (int)$name < $this->length);
    }

    function offsetGet($name) {
        $this->mSqlCommand->db_data_seek($this->mResule,$name);
        return $this->mSqlCommand->db_fetch_array($this->mResule, $this->mResultType);
    }

    function offsetSet($name, $id) {
    }

    function offsetUnset($name) {
    }
}
?>