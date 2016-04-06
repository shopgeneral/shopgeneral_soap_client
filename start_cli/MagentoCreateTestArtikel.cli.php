<?php

require_once realpath(dirname(__FILE__).'/../').'/config/basic.inc.php';
require_once ROOT.'examples/GetItemsBase/MagentoItem.class.php';

MagentoItem::getInstance()->getItem();

?>