<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 25/11/16
 * Time: 09:14
 */

namespace App\Pdf\NtPdf\Table\Cell;


class TableEmpty extends NtAbstract
{
    public function isSplittable()
    {
        return false;
    }


    public function render()
    {
        $this->renderCellLayout();
    }

    public function copyProperties( NtAbstract $oSource )
    {
        $aProps = array_keys( $this->aDefaultValues );

        foreach ( $aProps as $sProperty )
        {
            if ( $oSource->isPropertySet( $sProperty ) )
            {
                $this->$sProperty = $oSource->$sProperty;
            }
        }

        //set 0 padding
        $this->setPadding();
    }
}