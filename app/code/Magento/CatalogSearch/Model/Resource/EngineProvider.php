<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Catalog Search engine provider
 */
namespace Magento\CatalogSearch\Model\Resource;

use Magento\Store\Model\ScopeInterface;

class EngineProvider
{
    const CONFIG_ENGINE_PATH = 'catalog/search/engine';

    /**
     * @var \Magento\CatalogSearch\Model\Resource\EngineInterface
     */
    protected $engine;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Pool of existing engines
     *
     * @var array
     */
    private $enginePool;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $engines
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $engines
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->objectManager = $objectManager;
        $this->enginePool = $engines;
    }

    /**
     * Get engine singleton
     *
     * @return \Magento\CatalogSearch\Model\Resource\EngineInterface
     */
    public function get()
    {
        if (!$this->engine) {
            $currentEngine = $this->scopeConfig->getValue(self::CONFIG_ENGINE_PATH, ScopeInterface::SCOPE_STORE);
            if (!isset($this->enginePool[$currentEngine])) {
                throw new \LogicException(
                    'There is no such engine: ' . $currentEngine
                );
            }
            $engineClassName = $this->enginePool[$currentEngine];

            $engine = $this->objectManager->create($engineClassName);
            if (false === $engine instanceof \Magento\CatalogSearch\Model\Resource\EngineInterface) {
                throw new \LogicException(
                    $engineClassName . ' doesn\'t implement \Magento\CatalogSearch\Model\Resource\EngineInterface'
                );
            }
            if ($engine && $engine->test()) {
                $this->engine = $engine;
            }
        }

        return $this->engine;
    }
}
