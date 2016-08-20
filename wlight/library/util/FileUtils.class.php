<?php
/**
 * 文件操作辅助类
 * @author KavMors(kavmors@163.com)
 *
 * string UNIT_BYTE
 * string UNIT_KB
 * string UNIT_MB
 * string UNIT_GB
 * string UNIT_TB
 *
 * boolean exists(string)
 * string dirOrFile(string)
 * boolean createDir(string, int)
 * boolean createFile(string, int)
 * boolean delete(string)
 * boolean rename(string, string)
 * boolean copy(string, string, boolean)
 * boolean move(string, string, boolean)
 * boolean merge(string, string, boolean)
 * string getAbsolutePath(string)
 * string getDir(string)
 * string getFileName(string, boolean)
 * string getExtension(string)
 * string getSize(string, string)
 * array listDir(string, boolean)
 * boolean isVisible(string)
 */

namespace wlight\util;

class FileUtils {
  const UNIT_BYTE = 'BYTE';
  const UNIT_KB = 'KB';
  const UNIT_MB = 'MB';
  const UNIT_GB = 'GB';
  const UNIT_TB = 'TB';

  /**
   * 判断文件是否存在
   * @param string $file 文件路径
   * @return boolean 文件不存在时返回false
   */
  public function exists($file) {
    $this->clear();
    return !empty($file) && @file_exists($file);
  }

  /**
   * 判断该文件为目录或文件类型
   * @param string $file 文件路径
   * @return string 返回'dir'或'file',文件不存在时返回空串
   */
  public function dirOrFile($file) {
    $this->clear();
    if (is_dir($file)) {
      return 'dir';
    }
    if (is_file($file)) {
      return 'file';
    }
    return '';
  }

  /**
   * 创建空目录
   * @param string $dir 目录路径
   * @param int $mode 目录权限,默认0775
   * @return boolean 创建成功时返回true,失败或目录已存在返回false
   */
  public function createDir($dir, $mode=0775) {
    $this->clear();
    return !$this->exists($dir) && @mkdir($dir, $mode, true);
  }

  /**
   * 创建文件
   * @param string $file 文件名
   * @param int $mode 文件权限,默认0775
   * @return boolean 创建成功时返回true,失败或文件已存在返回false
   */
  public function createFile($file, $mode=0775) {
    $this->clear();
    if ($this->exists($file)) {
      return false;
    }
    $dir = pathinfo($file, PATHINFO_DIRNAME);
    $this->createDir($dir, $mode);
    return @touch($file) && @chmod($file, $mode);
  }

  /**
   * 删除文件或目录
   * @param string $file 文件路径
   * @return boolean 删除成功返回true
   */
  public function delete($file) {
    $this->clear();
    $type = $this->dirOrFile($file);
    if ($type == 'file') {
      return @unlink($file);
    } elseif ($type == 'dir') {
      $handle = @opendir($file);
      while (($subfile = @readdir($handle)) !== false) {
        if ($subfile != '.' && $subfile != '..') {
          $this->delete("$file/$subfile");
        }
      }
      @closedir($handle);
      return @rmdir($file);
    } else {
      return false;
    }
  }

  /**
   * 重命名文件(此方式只改变路径下的文件名,不改变目录路径,需要修改路径请用move方式)
   * @param string $file 文件路径
   * @param string $name 新文件名
   * @return boolean 修改成功返回true
   */
  public function rename($file, $name) {
    $this->clear();
    if (!$this->exists($file) || $this->exists($name)) {
      return false;
    }
    $dir = $this->getDir($file);
    return @rename($file, "$dir/$name");
  }

  /**
   * 复制文件
   * @param string $source 源路径
   * @param string $destination 目标路径
   * @param boolean $overwrite 是否覆盖已有文件,默认覆盖
   * @return boolean 复制成功返回true
   */
  public function copy($source, $destination, $overwrite=true) {
    $this->clear();
    $type = $this->dirOrFile($source);

    if ($type == '') {
      return false;
    }

    if ($this->exists($destination)) {
      if ($overwrite) {
        $this->delete($destination);
      } else {
        return false;
      }
    }

    if ($type == 'file') {
      $this->createDir(pathinfo($destination, PATHINFO_DIRNAME), 0775);
      return @copy($source, $destination);
    } elseif ($type == 'dir') {
      $this->createDir($destination);
      $handle = @opendir($source);
      while (($subfile = @readdir($handle)) !== false) {
        if ($subfile != '.' && $subfile != '..') {
          $this->copy("$source/$subfile", "$destination/$subfile");
        }
      }
      @closedir($handle);
      return true;
    }
  }

  /**
   * 移动文件
   * @param string $source 源路径
   * @param string $destination 目标路径
   * @param boolean $overwrite 是否覆盖已有文件,默认覆盖
   * @return boolean 复制成功返回true
   */
  public function move($source, $destination, $overwrite=true) {
    $this->clear();
    $type = $this->dirOrFile($source);

    if ($type == '') {
      return false;
    }

    if ($this->exists($destination)) {
      if ($overwrite) {
        $this->delete($destination);
      } else {
        return false;
      }
    }

    if ($type == 'file') {
      $this->createDir(pathinfo($destination, PATHINFO_DIRNAME), 0775);
      return @rename($source, $destination);
    } elseif ($type == 'dir') {
      $this->createDir($destination);
      $handle = @opendir($source);
      while (($subfile = @readdir($handle)) !== false) {
        if ($subfile != '.' && $subfile != '..') {
          $this->move("$source/$subfile", "$destination/$subfile");
        }
      }
      @closedir($handle);
      @rmdir($source);
      return true;
    }
  }

  /**
   * 合并两个文件夹(源文件夹将被删除)
   * @param string $source 源路径
   * @param string $destination 目标路径
   * @param boolean $overwrite 是否覆盖同目录下的已有文件,默认覆盖
   * @return boolean 复制成功返回true
   */
  public function merge($source, $destination, $overwrite=true) {
    $this->clear();
    $srcType = $this->dirOrFile($source);
    $dtnType = $this->dirOrFile($destination);
    if ($srcType == '') {
      return false;
    }
    if ($srcType == 'dir' && $dtnType == '') {
      return $this->move($source, $destination);
    }
    if ($srcType != 'dir' || $dtnType != 'dir') {
      return false;
    }

    $handle = @opendir($source);
    while (($subfile = @readdir($handle)) !== false) {
      if ($subfile != '.' && $subfile != '..') {
        if (is_dir("$source/$subfile")) {
          $this->merge("$source/$subfile", "$destination/$subfile", $overwrite);
        } elseif (is_file("$source/$subfile")) {
          if (!$this->move("$source/$subfile", "$destination/$subfile", $overwrite)) {
            $this->delete("$source/$subfile");
          }
        }
      }
    }
    @closedir($handle);
    @rmdir($source);
    return true;
  }

  /**
   * 返回文件绝对路径
   * @param string $file 文件名
   * @return string 绝对路径,文件不存在则返回空串
   */
  public function getAbsolutePath($file) {
    $this->clear();
    $realpath = realpath($file);
    return $realpath ? $realpath : '';
  }

  /**
   * 返回文件路径
   * @param string $file 文件名
   * @return string 路径,文件不存在时返回false
   */
  public function getDir($file) {
    $this->clear();
    if (!$this->exists($file)) {
      return '';
    }
    return @pathinfo($file, PATHINFO_DIRNAME);
  }

  /**
   * 返回路径中文件名
   * @param string $file 文件路径
   * @param boolean $extension 是否返回后缀名,默认返回
   * @return string 文件名
   */
  public function getFileName($file, $extension=true) {
    $this->clear();
    if (!$this->exists($file)) {
      return '';
    }
    return @pathinfo($file, $extension ? PATHINFO_BASENAME : PATHINFO_FILENAME);
  }

  /**
   * 返回文件的后缀名
   * @param string $file 文件路径
   * @return string/boolean 后缀名,文件不存在时返回false
   */
  public function getExtension($file) {
    $this->clear();
    if (!$this->exists($file)) {
      return '';
    }
    return @pathinfo($file, PATHINFO_EXTENSION);
  }

  /**
   * 返回文件大小
   * @param string $file 文件路径
   * @param string $sizeUnit 单位,默认byte
   * @return float 文件大小,文件不存在或执行失败时返回0
   */
  public function getSize($file, $sizeUnit=self::UNIT_BYTE) {
    $this->clear();
    $sizeUnit = strtoupper($sizeUnit);
    $size = 0.0;

    $type = $this->dirOrFile($file);
    if ($type == 'file') {
      $size = @floatval(filesize($file));
    } elseif ($type == 'dir') {
      $handle = @opendir($file);
      while (($subfile = @readdir($handle)) !== false) {
        if ($subfile != '.' && $subfile != '..') {
          $size += $this->getSize("$file/$subfile");
        }
      }
      @closedir($handle);
    } else {
      return 0.0;
    }

    switch ($sizeUnit) {
      case self::UNIT_BYTE: break;
      case self::UNIT_KB: $size = $size / 1024.0; break;
      case self::UNIT_MB: $size = $size / 1024.0 / 1024.0; break;
      case self::UNIT_GB: $size = $size / 1024.0 / 1024.0 / 1024.0; break;
      case self::UNIT_TB: $size = $size / 1024.0 / 1024.0 / 1024.0 / 1024.0; break;
      default: break;
    }
    return $size;
  }

  /**
   * 列出目录所有子文件和子目录
   * @param string $dir 目录路径
   * @param array 子文件数组
   */
  public function listDir($dir, $subfile = false) {
    $this->clear();
    if (!$subfile) {
      return is_dir($dir) ? @glob("$dir/*") : null;
    } else {
      $l = @glob("$dir/*");
      foreach ($l as $key => $value) {
        if (is_dir($value)) {
          $l[$key] = array($value => $this->listDir($value, true));
        }
      }
      return $l;
    }
  }

  /**
   * 判断文件是否可见(非隐藏文件)
   * @param string $file 文件路径
   * @return boolean 文件隐藏或不存在时返回false
   */
  public function isVisible($file) {
    $this->clear();
    return $this->exists($file) && substr($this->getFileName($file), 0, 1) != '.';
  }

  //清理文件函数缓存
  private function clear() {
    @clearstatcache();
  }
}
?>