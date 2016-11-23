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
#region DASH PLUGIN
    function SetDash($black=null, $white=null)
    {
        if($black!==null)
            $s=sprintf('[%.3F %.3F] 0 d',$black*$this->k,$white*$this->k);
        else
            $s='[] 0 d';
        $this->_out($s);
    }
#endregion

#region ALPHA PLUGIN
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
#endregion

#region CIRCLE/ELLIPSE PLUGIN
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
#endregion

#region SHADOW CELL
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
#endregion

#region FITCELL
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
#endregion

#region LINE GRAPH
    public function getMax($arrData)
    {
        $max = 0;
        for ($ii = 0; $ii < count($arrData); $ii++) {
            for ($i = 0; $i < count($arrData[$ii]['data']); $i++) {
                if ($arrData[$ii]['data'][$i]['value'] > $max) $max = $arrData[$ii]['data'][$i]['value'];
            }
        }
        // set round to maximum
        $mxratio = ($max < 1000 ? 1.6 : 1.2);

        $max = round($max * $mxratio);
        $max = ceil(round($max / 1000, 1) * 1000);
        $max = ($max > 0 ? $max : 50);

        return $max;

    }

    public function LineChart($x, $y, $w, $h, $title, $arrData)
    {
        // get max value
        $max = $this->getMax($arrData);
        $hasTitle = false;

        if ($hasTitle) {
            // define title
            $this->SetTextColor(155, 155, 155);
            $this->SetLineWidth(0.3);
            $this->SetFont('arial', 'b', 12);
            $this->SetXY($x, $y - 2);
            $this->Cell(150, 6, $title, 0, 0, 'L');
            $this->SetDrawColor(170, 170, 170);
            $this->SetLineWidth(0.3);
            $this->Line($x, ($y + 3.5), 125, ($y + 3.5));
            $this->SetDrawColor(200, 200, 200);
            $this->Line($x, ($y + 3.7), 125, ($y + 3.7));
            // define lines title
            $this->SetFont('arial', '', 7);
            $wTitle = round(($w) / (count($arrData)));

            for ($i = 0; $i < count($arrData); $i++) {
                if (isset($arrData[$i]['title'])) {
                    $this->GraphCircle($x + 0.5 + ($wTitle * $i), $y + 10, 1.6, 0, 360, 'F', '', array($arrData[$i]['color'][0], $arrData[$i]['color'][1], $arrData[$i]['color'][2]));
                    $this->Text($x + 4 + ($wTitle * $i), $y + 11, $arrData[$i]['title']);
                }
            }

            $curY = $y + 12;
        } else {
            $curY = $y;
        }
        // draw axis
       /* $this->SetLineWidth(0.01);
        $this->SetDrawColor(200, 200, 200);
        $this->Line($x, ($curY + $h - 2), $w, ($curY + $h - 2));
*/
        // get axis X points
        $px = round(($w) / (count($arrData[0]['data'])), 2);
        $pyini = ($curY + 2.5);
        $pyend = ($curY + $h - 2);
        $pylong = $pyend - $pyini;
/*
        $this->SetDrawColor(240, 240, 240);
        $this->SetLineWidth(0.001);

        $this->Line($x, ($curY + $h - $pylong), $w, ($curY + $h - $pylong));

        $this->Line($x, ($curY + $h - ($pylong / 2)), $w, ($curY + $h - ($pylong / 2)));
*/
        $this->SetLineWidth(0.5);

        $this->SetFont('arial', '', 5);

        // define Zero Zone
        $zero = $curY + $h - 2;

        for ($ii = 0; $ii < count($arrData); $ii++) {

            for ($i = 0; $i < count($arrData[$ii]['data']); $i++) {

                $this->SetLineWidth(0.3);

                // show horizontal text
                //$this->Text(($x+($i*$px)),$curY+$h,$arrData[$ii]['data'][$i]['key']);

                // change color
                $this->SetDrawColor($arrData[$ii]['color'][0], $arrData[$ii]['color'][1], $arrData[$ii]['color'][2]);

                // get scale
                $yesc = round(($pylong / $max) * $arrData[$ii]['data'][$i]['value']);

                // calculate each point
                $xpnt = ($x + ($i * $px));
                $ypnt = $zero - $yesc;

                // draw point
                $this->GraphCircle($xpnt, $ypnt, 0.6, 0, 360, 'DF', '', [255, 255, 255]);

                // get next point

                if ($i < (count($arrData[$ii]['data']) - 1)) {
                    $xpnt2 = ($x + (($i + 1) * $px));
                    $ypnt2 = $zero - round(($pylong / $max) * $arrData[$ii]['data'][$i + 1]['value']);
                } else {
                    $xpnt2 = $xpnt;
                    $ypnt2 = $ypnt;
                }

                // draw the line
                $this->Line($xpnt, $ypnt, $xpnt2, $ypnt2);

            }

        }

       // $this->SetTextColor(255, 255, 255);

       // $this->Text($x + 0.1, $curY + $h - $pylong + 1.9, $max);

        //$this->Text($x + 0.1, ($curY + $h - round($pylong / 2) + 1.9), round($max / 2));

        //$this->SetTextColor(99, 99, 99);

       // $this->Text($x, $curY + $h - $pylong + 1.8, $max);

        //$this->Text($x, ($curY + $h - round($pylong / 2) + 1.8), round($max / 2));

    }

    public function GraphEllipse($x0, $y0, $rx, $ry = 0, $angle = 0, $astart = 0, $afinish = 360, $style = '', $line_style = null, $fill_color = null, $nSeg = 8)
    {
        if ($rx) {
            if (!(false === strpos($style, 'F')) && $fill_color) {
                list($r, $g, $b) = $fill_color;
                $this->SetFillColor($r, $g, $b);
            }
            switch ($style) {
                case 'F':
                    $op = 'f';
                    $line_style = null;
                    break;
                case 'FD':
                case 'DF':
                    $op = 'B';
                    break;
                case 'C':
                    $op = 's'; // small 's' means closing the path as well
                    break;
                default:
                    $op = 'S';
                    break;
            }
            if ($line_style)
                $this->SetLineStyle($line_style);
            if (!$ry)
                $ry = $rx;
            $rx *= $this->k;
            $ry *= $this->k;
            if ($nSeg < 2)
                $nSeg = 2;

            $astart = deg2rad((float)$astart);
            $afinish = deg2rad((float)$afinish);
            $totalAngle = $afinish - $astart;

            $dt = $totalAngle / $nSeg;
            $dtm = $dt / 3;

            $x0 *= $this->k;
            $y0 = ($this->h - $y0) * $this->k;
            if ($angle != 0) {
                $a = -deg2rad((float)$angle);
                $this->_out(sprintf('q %.2f %.2f %.2f %.2f %.2f %.2f cm', cos($a), -1 * sin($a), sin($a), cos($a), $x0, $y0));
                $x0 = 0;
                $y0 = 0;
            }

            $t1 = $astart;
            $a0 = $x0 + ($rx * cos($t1));
            $b0 = $y0 + ($ry * sin($t1));
            $c0 = -$rx * sin($t1);
            $d0 = $ry * cos($t1);
            $this->_Point($a0 / $this->k, $this->h - ($b0 / $this->k));
            for ($i = 1; $i <= $nSeg; $i++) {
                // Draw this bit of the total curve
                $t1 = ($i * $dt) + $astart;
                $a1 = $x0 + ($rx * cos($t1));
                $b1 = $y0 + ($ry * sin($t1));
                $c1 = -$rx * sin($t1);
                $d1 = $ry * cos($t1);
                $this->_Curve(($a0 + ($c0 * $dtm)) / $this->k,
                    $this->h - (($b0 + ($d0 * $dtm)) / $this->k),
                    ($a1 - ($c1 * $dtm)) / $this->k,
                    $this->h - (($b1 - ($d1 * $dtm)) / $this->k),
                    $a1 / $this->k,
                    $this->h - ($b1 / $this->k));
                $a0 = $a1;
                $b0 = $b1;
                $c0 = $c1;
                $d0 = $d1;
            }
            $this->_out($op);
            if ($angle != 0)
                $this->_out('Q');
        }
    }

    // Draws a circle
    // Parameters:
    // - x0, y0: Center point
    // - r: Radius
    // - astart: Start angle
    // - afinish: Finish angle
    // - style: Style of circle (draw and/or fill) (D, F, DF, FD, C (D + close))
    // - line_style: Line style for circle. Array like for SetLineStyle
    // - fill_color: Fill color. Array with components (red, green, blue)
    // - nSeg: Ellipse is made up of nSeg BÃ©zier curves
    public function GraphCircle($x0, $y0, $r, $astart = 0, $afinish = 360, $style = '', $line_style = null, $fill_color = null, $nSeg = 8)
    {
        $this->GraphEllipse($x0, $y0, $r, 0, 0, $astart, $afinish, $style, $line_style, $fill_color, $nSeg);
    }

    private function _Point($x, $y)
    {
        $this->_out(sprintf('%.2f %.2f m', $x * $this->k, ($this->h - $y) * $this->k));
    }

    private function _Line($x, $y)
    {
        $this->_out(sprintf('%.2f %.2f l', $x * $this->k, ($this->h - $y) * $this->k));
    }

    function _Curve($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c', $x1 * $this->k, ($this->h - $y1) * $this->k, $x2 * $this->k, ($this->h - $y2) * $this->k, $x3 * $this->k, ($this->h - $y3) * $this->k));
    }
#endregion
}