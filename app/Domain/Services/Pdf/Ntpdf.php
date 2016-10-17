<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 17/10/16
 * Time: 18:32
 */

namespace App\Domain\Services\Pdf;


use Anouar\Fpdf\Fpdf;

class Ntpdf extends Fpdf
{
    /* ***************************************************
     * ALPHA PLUGIN
     * **************************************************/
    var $extgstates = [];

    function SetAlpha($alpha, $bm = 'Normal')
    {
        // set alpha for stroking (CA) and non-stroking (ca) operations
        $gs = $this->AddExtGState(array('ca' => $alpha, 'CA' => $alpha, 'BM' => '/' . $bm));
        $this->SetExtGState($gs);
    }

    function AddExtGState($parms)
    {
        $n = count($this->extgstates) + 1;
        $this->extgstates[$n]['parms'] = $parms;
        return $n;
    }

    function SetExtGState($gs)
    {
        $this->_out(sprintf('/GS%d gs', $gs));
    }

    function _enddoc()
    {
        if (!empty($this->extgstates) && $this->PDFVersion < '1.4')
            $this->PDFVersion = '1.4';
        parent::_enddoc();
    }

    function _putextgstates()
    {
        for ($i = 1; $i <= count($this->extgstates); $i++) {
            $this->_newobj();
            $this->extgstates[$i]['n'] = $this->n;
            $this->_out('<</Type /ExtGState');
            $parms = $this->extgstates[$i]['parms'];
            $this->_out(sprintf('/ca %.3F', $parms['ca']));
            $this->_out(sprintf('/CA %.3F', $parms['CA']));
            $this->_out('/BM ' . $parms['BM']);
            $this->_out('>>');
            $this->_out('endobj');
        }
    }

    function _putresourcedict()
    {
        parent::_putresourcedict();
        $this->_out('/ExtGState <<');
        foreach ($this->extgstates as $k => $extgstate)
            $this->_out('/GS' . $k . ' ' . $extgstate['n'] . ' 0 R');
        $this->_out('>>');
    }

    function _putresources()
    {
        $this->_putextgstates();
        parent::_putresources();
    }


    /* ***************************************************
     * CIRCLE / ELLIPSE PLUGIN
     * **************************************************/
    function Circle($x, $y, $r, $style = 'D')
    {
        $this->Ellipse($x, $y, $r, $r, $style);
    }

    function Ellipse($x, $y, $rx, $ry, $style = 'D')
    {
        if ($style == 'F')
            $op = 'f';
        elseif ($style == 'FD' || $style == 'DF')
            $op = 'B';
        else
            $op = 'S';
        $lx = 4 / 3 * (M_SQRT2 - 1) * $rx;
        $ly = 4 / 3 * (M_SQRT2 - 1) * $ry;
        $k = $this->k;
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x + $rx) * $k, ($h - $y) * $k,
            ($x + $rx) * $k, ($h - ($y - $ly)) * $k,
            ($x + $lx) * $k, ($h - ($y - $ry)) * $k,
            $x * $k, ($h - ($y - $ry)) * $k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x - $lx) * $k, ($h - ($y - $ry)) * $k,
            ($x - $rx) * $k, ($h - ($y - $ly)) * $k,
            ($x - $rx) * $k, ($h - $y) * $k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            ($x - $rx) * $k, ($h - ($y + $ly)) * $k,
            ($x - $lx) * $k, ($h - ($y + $ry)) * $k,
            $x * $k, ($h - ($y + $ry)) * $k));
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c %s',
            ($x + $lx) * $k, ($h - ($y + $ry)) * $k,
            ($x + $rx) * $k, ($h - ($y + $ly)) * $k,
            ($x + $rx) * $k, ($h - $y) * $k,
            $op));
    }

    /* ***************************************************
     * SHADOW CELL
     * **************************************************/
    function ShadowCell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '', $color = 'G', $distance = 0.5, $shadowAlpha = 0.32)
    {
        if ($color == 'G')
            $ShadowColor = [100, 100, 100];
        elseif ($color == 'B')
            $ShadowColor = [0, 0, 0];
        else
            $ShadowColor = $color;
        $TextColor = $this->TextColor;
        $x = $this->x;
        $this->x += $distance;
        $this->y += $distance;
        $this->SetAlpha($shadowAlpha);
        call_user_func_array([$this, 'SetTextColor'], $ShadowColor);
        $this->Cell($w, $h, $txt, $border, 0, $align, $fill, $link);
        $this->SetAlpha(1);
        $this->TextColor = $TextColor;
        $this->x = $x;
        $this->y -= $distance;
        $this->Cell($w, $h, $txt, 0, $ln, $align);
    }

    public function FittCell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '')
    {
        if ($w == 0) {
            $w = $this->pageWidth();
        }
        $decrement_step = .1;
        $fs = $this->FontSize;
        while ($this->GetStringWidth($txt) > $w) {
            $this->SetFontSize($this->FontSize -= $decrement_step);
        }
        $this->Cell($w, $h, $txt, $border, $ln, $align);
        //reset font size
        $this->SetFontSize($fs);
    }

    public function ShadowFittCell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '', $color = 'G', $distance = 0.5, $shadowAlpha = 0.32)
    {
        if ($w == 0) {
            $w = $this->pageWidth();
        }
        $decrement_step = .1;
        $fs = $this->FontSize;
        while ($this->GetStringWidth($txt) > $w) {
            $this->SetFontSize($this->FontSize -= $decrement_step);
        }
        $this->ShadowCell($w, $h, $txt, $border, $ln, $align, $fill, $link, $color, $distance, $shadowAlpha);
        //reset font size
        $this->SetFontSize($fs);
    }

    public function pageWidth()
    {
        return $this->w;
        $leftMargin = $this->lMargin;
        $rightMargin = $this->rMargin;
        return $width - $rightMargin - $leftMargin;
    }
}