<?PHP
/**
 * $Id: Runnable.php 106 2008-04-03 10:34:53Z matao $
 * @package KISS
 */
/**
 * 可以直接运行的程序必须实现此接口
 * @package KISS_Interface
 */
interface KISS_Interface_Runnable {
    public function run();
}
?>