<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Block_Adminhtml_Widget_Grid_Column_Renderer_Log_Message
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row) {

        switch ($row->getStatus()) {
            case 'error':
                $color = 'red';
                break;
            case 'warning':
                $color = 'orange';
                break;
            default:
                $color = 'green';
                break;
        }

        return '<span style="color:'.$color.'">'.$row->getMessage().'</span>';
    }

    public function renderExport(Varien_Object $row) {
        return $row->getMessage();
    }
}
