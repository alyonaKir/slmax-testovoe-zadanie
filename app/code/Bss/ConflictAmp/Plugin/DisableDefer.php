<?php
namespace Bss\ConflictAmp\Plugin;

class DisableDefer
{
    protected $ampConfig;

    protected $request;

    public function __construct(
        \Zemez\Amp\Helper\Data $ampConfig,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->ampConfig = $ampConfig;
        $this->request = $request;
    }

    public function afterIsEnabled(\Bss\DeferJS\Helper\Data $subject, $result)
    {
        if ($this->ampConfig->extEnabled() && $this->request->getParam('amp') == 1) {
            return false;
        }
        return $result;
    }
}
