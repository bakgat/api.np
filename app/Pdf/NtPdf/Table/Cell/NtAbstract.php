<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 25/11/16
 * Time: 09:13
 */

namespace App\Pdf\NtPdf\Table\Cell;


use App\Pdf\NtPdf\NtInterface;
use App\Pdf\NtPdf\Tools;
use App\Pdf\NtPdf\Validate;
use App\Pdf\Pdf;

abstract class NtAbstract implements TableInterface
{
    protected $aPropertyMethodMap = array(
        'ALIGN' => 'setAlign',
        'VERTICAL_ALIGN' => 'setAlignVertical',
        'COLSPAN' => 'setColSpan',
        'ROWSPAN' => 'setRowSpan',
        'PADDING' => 'setPadding',
        'PADDING_TOP' => 'setPaddingTop',
        'PADDING_RIGHT' => 'setPaddingRight',
        'PADDING_BOTTOM' => 'setPaddingBottom',
        'PADDING_LEFT' => 'setPaddingLeft',
        'BORDER_TYPE' => 'setBorderType',
        'BORDER_SIZE' => 'setBorderSize',
        'BORDER_COLOR' => 'setBorderColor',
        'BACKGROUND_COLOR' => 'setBackgroundColor',
        'ROTATE' => 'setRotate',
    );

    /**
     * Colspan
     *
     * @var int
     */
    protected $colSpan = 1;

    /**
     * Rowspan
     *
     * @var int
     */
    protected $rowSpan = 1;

    protected $paddingTop = 0;
    protected $paddingRight = 0;
    protected $paddingBottom = 0;
    protected $paddingLeft = 0;

    protected $backgroundColor = array( 255, 255, 255 );

    protected $borderType = '1';
    protected $borderSize = 0.1;
    protected $borderColor = array( 0, 0, 0 );

    protected $align = 'L';
    protected $alignVertical = 'M';
    protected $rotate = 0;

    protected $aProperties = array();

    protected $aInternValueSet = array();

    protected $nCellWidth = 0;

    protected $nCellHeight = 0;

    protected $nCellDrawWidth = 0;

    protected $nCellDrawHeight = 0;

    protected $nContentWidth = 0;

    protected $nContentHeight = 0;

    /**
     * Default alignment is Middle Center
     *
     * @var string
     */
    protected $sAlignment = 'MC';

    /**
     * Pdf Interface
     *
     * @var Pdf
     */
    protected $oPdf;

    /**
     * Pdf Interface
     *
     * @var NtInterface
     */
    protected $oPdfi;

    /**
     * If this cell will be skipped
     *
     * @var boolean
     */
    protected $bSkip = false;


    public function __construct( $pdf )
    {
        if ( $pdf instanceof NtInterface )
        {
            $this->oPdfi = $pdf;
            $this->oPdf = $pdf->getPdfObject();
        }
        else
        {
            //it must be an instance of a pdf object
            $this->oPdf = $pdf;
            $this->oPdfi = new NtInterface( $pdf );
        }
    }

    public function setProperties( array $aValues = array() )
    {
        $this->setInternValues( $aValues, false );
    }

    /**
     * Sets the intern variable values
     *
     * @param array $aValues The values to be set
     * @param bool $bCheckSet If the values are already set, the values will NOT be set
     */
    protected function setInternValues( array $aValues = array(), $bCheckSet = true )
    {
        foreach ( $aValues as $key => $value )
        {
            if ( $bCheckSet && $this->isInternValueSet( $key ) )
            {
                //property is already set, ignore the value
                continue;
            }

            $this->setInternValue( $key, $value );
        }
    }


    /**
     * Returns true if the property is already set
     *
     * @param string $key
     * @return bool
     */
    protected function isInternValueSet( $key )
    {
        return array_key_exists( $key, $this->aInternValueSet );
    }

    /**
     * Marks the property as set
     *
     * @param string $key
     */
    protected function markInternValueAsSet( $key )
    {
        $this->aInternValueSet[ $key ] = true;
    }

    /**
     * Sets an intern value
     *
     * @param $key
     * @param $value
     */
    protected function setInternValue( $key, $value )
    {
        $this->markInternValueAsSet( $key );

        if ( isset( $this->aPropertyMethodMap[ $key ] ) )
        {
            call_user_func_array( array(
                $this,
                $this->aPropertyMethodMap[ $key ]
            ), Tools::makeArray( $value ) );

            return;
        }

        $method = "set" . ucfirst( $key );

        if ( method_exists( $this, $method ) )
        {
            call_user_func_array( array(
                $this,
                $method
            ), Tools::makeArray( $value ) );

            return;
        }

        $this->aProperties[ $key ] = $value;
    }


    /**
     * Set image alignment.
     * It can be any combination of the 2 Vertical and Horizontal values:
     * Vertical values: TBM
     * Horizontal values: LRC
     *
     * @param string $alignment
     */
    public function setAlign( $alignment )
    {
        $this->sAlignment = strtoupper( $alignment );
    }


    public function setColSpan( $value )
    {
        $this->colSpan = Validate::intPositive( $value );
    }


    public function getColSpan()
    {
        return $this->colSpan;
    }


    public function setRowSpan( $value )
    {
        $this->rowSpan = Validate::intPositive( $value );
    }


    public function getRowSpan()
    {
        return $this->rowSpan;
    }


    public function setCellWidth( $value )
    {
        $value = Validate::float( $value, 0 );

        $this->nCellWidth = $value;

        if ( $value > $this->getCellDrawWidth() )
        {
            $this->setCellDrawWidth( $value );
        }
    }


    public function getCellWidth()
    {
        return $this->nCellWidth;
    }


    public function setCellHeight( $value )
    {
        $value = Validate::float( $value, 0 );

        $this->nCellHeight = $value;

        if ( $value > $this->getCellDrawHeight() )
        {
            $this->setCellDrawHeight( $value );
        }
    }


    public function getCellHeight()
    {
        return $this->nCellHeight;
    }


    public function setCellDrawHeight( $value )
    {
        $value = Validate::float( $value, 0 );

        if ( $this->getCellHeight() <= $value )
        {
            $this->nCellDrawHeight = $value;
        }
    }


    public function getCellDrawHeight()
    {
        return $this->nCellDrawHeight;
    }


    public function setCellDrawWidth( $value )
    {
        $value = Validate::float( $value, 0 );

        $this->nCellDrawWidth = $value;
        $this->setCellWidth( $value );
    }


    public function getCellDrawWidth()
    {
        return $this->nCellDrawWidth;
    }


    public function setContentWidth( $value )
    {
        $this->nContentWidth = Validate::float( $value, 0 );
    }


    public function getContentWidth()
    {
        return $this->nContentWidth;
    }


    public function setContentHeight( $value )
    {
        $this->nContentHeight = Validate::float( $value, 0 );
    }


    public function getContentHeight()
    {
        return $this->nContentHeight;
    }


    public function setSkipped( $value )
    {
        $this->bSkip = (bool) $value;
    }


    public function getSkipped()
    {
        return $this->bSkip;
    }


    public function __get( $property )
    {
        if ( isset( $this->aProperties[ $property ] ) )
        {
            return $this->aProperties[ $property ];
        }

        trigger_error( "Undefined property $property" );

        return null;
    }


    public function __set( $property, $value )
    {
        $this->setInternValue( $property, $value );

        return $this;
    }


    public function isPropertySet( $property )
    {
        if ( isset( $this->aProperties[ $property ] ) )
            return true;

        return false;
    }


    public function setDefaultValues( array $aValues = array() )
    {
        $this->setInternValues( $aValues, true );
    }


    /**
     * Renders the base cell layout - Borders and Background Color
     */
    public function renderCellLayout()
    {
        $x = $this->oPdf->GetX();
        $y = $this->oPdf->GetY();

        //border size BORDER_SIZE
        $this->oPdf->SetLineWidth( $this->getBorderSize() );

        if ( !$this->isTransparent() )
        {
            //fill color = BACKGROUND_COLOR
            list ( $r, $g, $b ) = $this->getBackgroundColor();
            $this->oPdf->SetFillColor( $r, $g, $b );
        }

        //Draw Color = BORDER_COLOR
        list ( $r, $g, $b ) = $this->getBorderColor();
        $this->oPdf->SetDrawColor( $r, $g, $b );

        $this->oPdf->Cell( $this->getCellDrawWidth(), $this->getCellDrawHeight(), '', $this->getBorderType(), 0, '', !$this->isTransparent() );

        $this->oPdf->SetXY( $x, $y );
    }


    protected function isTransparent()
    {
        return Tools::isFalse( $this->getBackgroundColor() );
    }


    public function copyProperties( NtAbstract $oSource )
    {
        $this->rowSpan = $oSource->getRowSpan();
        $this->colSpan = $oSource->getColSpan();

        $this->paddingTop = $oSource->getPaddingTop();
        $this->paddingRight = $oSource->getPaddingRight();
        $this->paddingBottom = $oSource->getPaddingBottom();
        $this->paddingLeft = $oSource->getPaddingLeft();

        $this->borderColor = $oSource->getBorderColor();
        $this->borderSize = $oSource->getBorderSize();
        $this->borderType = $oSource->getBorderType();

        $this->backgroundColor = $oSource->getBackgroundColor();

        $this->alignVertical = $oSource->getAlignVertical();
    }


    public function processContent()
    {
    }


    public function setPadding( $top = 0, $right = 0, $bottom = 0, $left = 0 )
    {
        $this->setPaddingTop( $top );
        $this->setPaddingRight( $right );
        $this->setPaddingBottom( $bottom );
        $this->setPaddingLeft( $left );
    }


    public function setPaddingBottom( $paddingBottom )
    {
        $this->paddingBottom = Validate::float( $paddingBottom, 0 );
    }


    public function getPaddingBottom()
    {
        return $this->paddingBottom;
    }


    public function setPaddingLeft( $paddingLeft )
    {
        $this->paddingLeft = Validate::float( $paddingLeft, 0 );
    }


    public function getPaddingLeft()
    {
        return $this->paddingLeft;
    }


    public function setPaddingRight( $paddingRight )
    {
        $this->paddingRight = Validate::float( $paddingRight, 0 );
    }


    public function getPaddingRight()
    {
        return $this->paddingRight;
    }


    public function setPaddingTop( $paddingTop )
    {
        $this->paddingTop = Validate::float( $paddingTop, 0 );
    }


    public function getPaddingTop()
    {
        return $this->paddingTop;
    }


    public function setBorderSize( $borderSize )
    {
        $this->borderSize = Validate::float( $borderSize, 0 );
    }


    public function getBorderSize()
    {
        return $this->borderSize;
    }


    public function setBorderType( $borderType )
    {
        $this->borderType = $borderType;
    }


    public function getBorderType()
    {
        return $this->borderType;
    }

    public function setBorderColor( $r, $b = null, $g = null )
    {
        $this->borderColor = Tools::getColor( $r, $b, $g );
    }

    public function getBorderColor()
    {
        return $this->borderColor;
    }


    public function setAlignVertical( $alignVertical )
    {
        $this->alignVertical = Validate::alignVertical( $alignVertical );
    }


    public function getAlignVertical()
    {
        return $this->alignVertical;
    }

    public function setBackgroundColor( $r, $b = null, $g = null )
    {
        $this->backgroundColor = Tools::getColor( $r, $b, $g );
    }

    public function getBackgroundColor()
    {
        return $this->backgroundColor;
    }

    /**
     * @return int
     */
    public function getRotate()
    {
        return $this->rotate;
    }

    /**
     * @param int $rotate
     */
    public function setRotate($rotate)
    {
        $this->rotate = $rotate;
    }

    public function split( $nRowHeight, $nMaxHeight )
    {
        return array( $this, 0 );
    }
}