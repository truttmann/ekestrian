<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace RepriseBatch\utils\sql\dbColumn;

use Zend\Db\Sql\Ddl\Column;
class TinyInteger extends Column\Integer
{
    /**
     * @var string
     */
    protected $type = 'TINYINT';
}
