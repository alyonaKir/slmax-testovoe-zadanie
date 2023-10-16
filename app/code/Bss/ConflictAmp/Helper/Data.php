<?php

namespace Bss\ConflictAmp\Helper;

class Data extends \Zemez\Amp\Helper\Data
{
    public function getProductListTemplate($name)
    {
        if ($this->extEnabled() && $this->_request->getParam('amp') == 1) {
            return 'Zemez_Amp::catalog/product/list.phtml';
        }
        return $name;
    }
}
