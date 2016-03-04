<?php
/**
 * 菜单设计辅助类
 * @author  KavMors(kavmors@163.com)
 * @since   2.0
 */

namespace wlight\menu;

class MenuDesigner {
  const PUSH = 'scancode_push';
  const WAITMSG = 'scancode_waitmsg';
  const SYSPHOTO = 'pic_sysphoto';
  const PHOTO_OR_ALBUM = 'pic_photo_or_album';
  const WEIXIN = 'pic_weixin';

  private $menu;

  public function __construct() {
    $this->menu = array();
  }

  /**
   * 获取通过本类方法生成的菜单数组
   * @return array - 菜单数组
   */
  public function getMenu() {
    return $this->menu;
  }

  /**
   * 添加一个子菜单
   * @param string $name - 子菜单标题
   * @param array $subButton - 子菜单数组, 可通过本类生成
   * @return array - 菜单生成数组
   */
  public function addSubButton($name, $subButton) {
    $button = array(
      'name' => $name,
      'sub_button' => $subButton
    );
    return $this->addMenu($button);
  }

  /**
   * 添加一个CLICK类型菜单
   * @param string $name - 菜单标题
   * @param string $key - 菜单key值
   * @return array - 菜单生成数组
   */
  public function addClick($name, $key) {
    $button = array(
      'type' => 'click',
      'name' => $name,
      'key' => $key
    );
    return $this->addMenu($button);
  }

  /**
   * 添加一个VIEW类型菜单
   * @param string $url - 网页链接
   * @param string $name - 菜单标题
   * @return array - 菜单生成数组
   */
  public function addView($name, $url) {
    $button = array(
      'type' => 'view',
      'name' => $name,
      'url' => $url
    );
    return $this->addMenu($button);
  }

  /**
   * 添加一个扫码类型菜单
   * @param string $name - 菜单标题
   * @param string $key - 菜单key值
   * @param string $type - 可选,扫码操作类型,可填PUSH或WAITMSG
   * @return array - 菜单生成数组
   */
  public function addScan($name, $key, $type='scancode_push') {
    $button = array(
      'type' => $type,
      'name' => $name,
      'key' => $key
    );
    return $this->addMenu($button);
  }

  /**
   * 添加一个发图类型菜单
   * @param string $name - 菜单标题
   * @param string $key - 菜单key值
   * @param string $type - 可选,发图类型,可填SYSPHOTO或PHOTO_OR_ALBUM或WEIXIN
   * @return array - 菜单生成数组
   */
  public function addPic($name, $key, $type='pic_photo_or_album') {
    $button = array(
      'type' => $type,
      'name' => $name,
      'key' => $key
    );
    return $this->addMenu($button);
  }

  /**
   * 添加一个发送位置类型菜单
   * @param string $name - 菜单标题
   * @param string $key - 菜单key值
   * @return array - 菜单生成数组
   */
  public function addLocation($name, $key) {
    $button = array(
      'type' => 'location_select',
      'name' => $name,
      'key' => $key
    );
    return $this->addMenu($button);
  }

  //将生成的菜单数组添加到menu后返回
  private function addMenu($button) {
    $this->menu[] = $button;
    return $button;
  }
}
?>