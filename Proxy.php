<?PHP
class KISS_Proxy {
    public $mObject = null; 
    public $mCurrentUser = null;

    public function __construct($pClassInstance,$pUser) {
        $this->mObject = $pClassInstance;
        $this->mCurrentUser = $pUser;
    }

    protected function Invoke($method, $parameters) {
        if(KISS_Util_Permission::InvokePermissions($this->mCurrentUser ,$this->mObject,$method)) {
            return call_user_func_array(array($this->mObject, $method), $parameters);
        }
        else {
            throw new Exception("х╗оч╡╩вЦ");
        }
    }
    
    function __set($member, $value) {
        $this->mObject->$member = $value;
    }
    
    function __get($member) {
        return $this->mObject->$member;
    }
    
    function __call($method, $paraments) {
        return $this->Invoke($method, $paraments);
    }
}
?>