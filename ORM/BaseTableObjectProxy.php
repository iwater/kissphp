<?PHP
/**
 * @author $Author: matao $
 * @version $Id: BaseTableObjectProxy.php 105 2008-04-03 10:26:50Z matao $
 * @package KISS
 * @subpackage ORM
 */
/**
 * KISS_ORM_BaseTableObject 的代理类
 * @package KISS
 * @subpackage ORM
 */
class KISS_ORM_BaseTableObjectProxy extends KISS_Proxy implements IteratorAggregate {
    public $mMapHash;
    public function __construct(KISS_ORM_BaseTableObject $pClassInstance,$pUser) {
        parent:: __construct($pClassInstance,$pUser);
        $this->mMapHash = $this->mObject->mMapHash;
    }

    function getIterator() {
        return $this->mObject->getIterator();
    }
}
?>