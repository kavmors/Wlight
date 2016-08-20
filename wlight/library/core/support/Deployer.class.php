<?php
/**
 * 初始化部署控制
 * @author  KavMors(kavmors@163.com)
 */

namespace wlight\core\support;

class Deployer {
  public static function initFileSystem() {
    self::createDir(RUNTIME_ROOT);
    self::createDir(RUNTIME_ROOT.'/cache');
    self::createDir(RUNTIME_ROOT.'/log');

    self::createDir(MSG_ROOT);
    self::createDir(MSG_ROOT.'/text');
    self::createDir(MSG_ROOT.'/image');
    self::createDir(MSG_ROOT.'/voice');
    self::createDir(MSG_ROOT.'/video');
    self::createDir(MSG_ROOT.'/shortvideo');
    self::createDir(MSG_ROOT.'/link');
    self::createDir(MSG_ROOT.'/location');
    self::createDir(MSG_ROOT.'/event');
    self::createDir(MSG_ROOT.'/event/subscribe');
    self::createDir(MSG_ROOT.'/event/unsubscribe');
    self::createDir(MSG_ROOT.'/event/CLICK');
    self::createDir(MSG_ROOT.'/event/SCAN');
    self::createDir(MSG_ROOT.'/event/LOCATION');
    self::createDir(MSG_ROOT.'/event/VIEW');

    self::createDir(APP_ROOT);
    self::createDir(RES_ROOT);

    self::move(DIR_ROOT.'/Hook.php', MSG_ROOT.'/Hook.php');
    self::move(DIR_ROOT.'/Sample.php', MSG_ROOT.'/text/Sample.php');
  }

  public static function initConfig() {
    $config = array(
      'WLIGHT' => WLIGHT,
      'WLIGHT_VERSION' => WLIGHT_VERSION,
      'APP_ID' => APP_ID,
      'APP_SECRET' => APP_SECRET,
      'APP_NAME' => APP_NAME,
      'WECHAT_ID' => WECHAT_ID,
      'TOKEN' => TOKEN,
      'ENCODING_AESKEY' => ENCODING_AESKEY,
      'DB_USER' => DB_USER,
      'DB_PWD' => DB_PWD,
      'DB_TYPE' => DB_TYPE,
      'DB_HOST' => DB_HOST,
      'DB_PORT' => DB_PORT,
      'DB_NAME' => DB_NAME,
      'DB_PREFIX' => DB_PREFIX,
      'DB_CHARSET' => DB_CHARSET,
      'DB_COLLATION' => DB_COLLATION,
      'DIR_ROOT' => DIR_ROOT,
      'APP_ROOT' => APP_ROOT,
      'RES_ROOT' => RES_ROOT,
      'MSG_ROOT' => MSG_ROOT,
      'RUNTIME_ROOT' => RUNTIME_ROOT,
      'HOST' => HOST,
      'PATH' => PATH,
      'WLIGHT_URL' => WLIGHT_URL,
      'APP_URL' => APP_URL,
      'RES_URL' => RES_URL,
      'DEBUG' => DEBUG,
      'DEFAULT_TIMEZONE' => DEFAULT_TIMEZONE,
      'DEFAULT_PERMISSION' => DEFAULT_PERMISSION,
      'STATIS_LIVE' => STATIS_LIVE,
      'LOG_LIVE' => LOG_LIVE,
      'MAX_CACHE' => MAX_CACHE
    );
    $config = json_encode($config);
    $file = RUNTIME_ROOT.'/cache/config.json.php';
    self::createFile($file);
    file_put_contents($file, "<?php exit; ?>\n".$config);
  }

  /*********** For file system *****************/

  private static function createDir($dir) {
    if (!is_dir($dir)) {
      @mkdir($dir);
      @chmod($dir, DEFAULT_PERMISSION);
    }
  }

  private static function createFile($file) {
    if (!file_exists($file)) {
      @touch($file);
      @chmod($file, DEFAULT_PERMISSION);
    }
  }

  private static function move($from, $to) {
    @rename($from, $to);
    @chmod($to, DEFAULT_PERMISSION);
  }
}
?>