<?php
/**
 * MageSpecialist
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magespecialist.it so we can send you a copy immediately.
 *
 * @category   MSP
 * @package    MSP_NoSpam
 * @copyright  Copyright (c) 2017 Skeeller srl (http://www.magespecialist.it)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace MSP\NoSpam\Setup;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Move config from srcPath to dstPath
     * @param ModuleDataSetupInterface $setup
     * @param string $srcPath
     * @param string $dstPath
     */
    private function moveConfig(ModuleDataSetupInterface $setup, $srcPath, $dstPath)
    {
        $value = $this->scopeConfig->getValue($srcPath);

        if (is_array($value)) {
            foreach (array_keys($value) as $k) {
                $this->moveConfig($setup, $srcPath . '/' . $k, $dstPath . '/' . $k);
            }
        } else {
            $connection = $setup->getConnection();
            $configData = $setup->getTable('core_config_data');
            $connection->update($configData, ['path' => $dstPath], 'path='.$connection->quote($srcPath));
        }
    }

    private function upgradeTo010200(ModuleDataSetupInterface $setup)
    {
        $this->moveConfig(
            $setup,
            'msp_securitysuite/nospam/actions_stop_list',
            'msp_securitysuite_nospam/general/actions_stop_list'
        );
        $this->moveConfig(
            $setup,
            'msp_securitysuite/nospam/actions_log_list',
            'msp_securitysuite_nospam/general/actions_log_list'
        );
        $this->moveConfig(
            $setup,
            'msp_securitysuite/nospam/honeypot_enabled',
            'msp_securitysuite_nospam/honeypot/enabled'
        );
        $this->moveConfig(
            $setup,
            'msp_securitysuite/nospam/honeypot_key',
            'msp_securitysuite_nospam/honeypot/key'
        );
        $this->moveConfig(
            $setup,
            'msp_securitysuite/nospam/honeypot_stop_list',
            'msp_securitysuite_nospam/honeypot/stop_list'
        );
    }

    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.2.0') < 0) {
            $this->upgradeTo010200($setup);
        }

        $setup->endSetup();
    }
}
