<?php
namespace Bss\ConflictAmp\Plugin;

class DisableProductLabel
{
    protected $ampConfig;

    public function __construct(
        \Zemez\Amp\Helper\Data $ampConfig
    ) {
        $this->ampConfig = $ampConfig;
    }

    public function afterIsEnable(\Bss\ProductLabel\Helper\Data $subject, $result)
    {
        if ($this->ampConfig->isAmpCall()) {
            return 0;
        }
        return $result;
    }
}
