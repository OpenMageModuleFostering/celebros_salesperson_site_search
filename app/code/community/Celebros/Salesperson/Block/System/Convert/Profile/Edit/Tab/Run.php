<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Convert profile edit tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Celebros_Salesperson_Block_System_Convert_Profile_Edit_Tab_Run extends Mage_Adminhtml_Block_System_Convert_Profile_Edit_Tab_Run
{
     public function getRunButtonHtml()
    {
        $profile_name=Mage::registry('current_convert_profile')->getName();


        $html = '';
        if ($profile_name == "Salesperson Exporter")
        {
            $export_URL = Mage::helper("adminhtml")->getUrl
                ("salesperson_admin/export/export_celebros/");

            $schedule_URL = Mage::helper("adminhtml")->getUrl
                ("salesperson_admin/export/schedule_export/");


            $html .= $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
                ->setLabel($this->__('Celebros Export Products'))
                ->setOnClick('window.open(\''.$export_URL.'\')')
                ->toHtml();

            $html .= '<br/><br/>';
            $html .= $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
            ->setLabel($this->__('Celebros Schedule Cron Export'))
            ->setOnClick('window.open(\''.$schedule_URL.'\')')
            ->toHtml();            
        }
        else
        {
            /* old button*/
            $html.=$this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
                ->setClass('save')->setLabel($this->__('Run Profile in Popup'))
                ->setOnClick('runProfile(true)')
                ->toHtml();
    /*end old button*/
        }
        return $html;

    }
}
