<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 18/10/16
 * Time: 08:59
 */

namespace App\Domain\Services\Pdf;

use App\Domain\Model\Evaluation\EvaluationRepository;
use App\Domain\Model\Evaluation\IAC;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Reporting\BranchResult;
use App\Domain\Model\Reporting\IacGoalResult;
use App\Domain\Model\Reporting\IacResult;
use App\Domain\Model\Reporting\MajorResult;
use App\Domain\Model\Reporting\RangeResult;
use App\Domain\Model\Reporting\Report;
use App\Domain\Model\Reporting\StudentResult;
use App\Domain\Model\Time\DateRange;
use App\Pdf\NtPdf\Multicell;
use App\Pdf\Pdf;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use IntlDateFormatter;


class Report2PdfService
{
    private $fontmap = [
        'B' => 'g',
        'C' => 'l',
        'T' => 'f',
        'S' => 'd',
        'CHECK' => 'n'
    ];

    private $leftMargin = 20;
    private $pointLeftMargin = 86;
    private $totalPointLeftMargin = 140;
    private $graphLeftMargin = 160;

    private $majorWidth = 0;
    private $branchWidth = 44;
    private $subPointWidth = 32;
    private $totalPointWidth = 11;
    private $graphWidth = 35;

    private $majorHeight = 8;
    private $branchHeight = 5;
    private $redicodiHeight = 5;
    private $majorTopMargin = 8;
    private $subPointHeight = 4;
    private $branchTopMargin = 3;
    private $subPointTopMargin = 1;
    private $totalPointTopMargin = 1;
    private $totalPointHeight = 8;
    private $branchBottomMargin = 1;

    private $graphHeight = 8;
    private $graphTopMargin = 1;

    private $majorFontSize = 18;
    private $branchFontSize = 12;
    private $subPointFontSize = 8;
    private $redicodiFontSize = 9;
    private $totalPointFontSize = 12;

    private $iacLeftMargin = 20;
    private $iacTextTopMargin = 2;
    private $iacTextHeight = 4;
    private $iacTextWidth = 80;
    private $iacCommentWidth = 50;
    private $iacIconWidth = 20;
    private $iacTextFontSize = 9;
    private $iacCommentFontSize = 8;
    private $iacIconFontSize = 12;


    /** @var EvaluationRepository */
    private $evaluationRepo;

    private $pdf;

    const ORANGE = [231, 155, 0];
    const BLUE = [0, 87, 157];

    /** @var Report */
    private $report;

    /** @var bool */
    private $frontPage = false;


    private $iacAchievedText = 'Gekend';
    private $iacPracticeText= 'Nog oefenen';
    private $iacCommentText = 'Commentaar';

    public function __construct(EvaluationRepository $evaluationRepository)
    {
        setlocale(LC_ALL, 'nl_BE');
        $this->evaluationRepo = $evaluationRepository;
        $this->pdf = new Pdf();
        $this->pdf->AddFont('Roboto', '', 'Roboto-Regular.php');
        $this->pdf->AddFont('Roboto', 'bold', 'Roboto-Bold.php');
        $this->pdf->AddFont('NotosIcon', '', 'NotosIcons.php');
        $this->pdf->SetAutoPageBreak(false, 7);
    }

    public function report(Report $report)
    {
        $this->report = $report;
        return $this;
    }

    public function withFrontPage()
    {
        $this->frontPage = true;
        return $this;
    }

    public function build()
    {

        /** @var StudentResult $result */
        foreach ($this->report->getStudentResults() as $result) {

            if ($this->frontPage) {
                $this->makeFrontPage($result);
            }

            $this->pdf->AddPage();

            $this->Header($result);

            $this->pdf->SetAutoPageBreak(true);

            $this->generateMajors($result);

            $this->Footer($result);
        }

        return $this->pdf->Output();
    }


    /**
     * @param $result
     */
    private function generateMajors(StudentResult $result)
    {
        /** @var MajorResult $majorResult */
        foreach ($result->getMajorResults() as $majorResult) {
            $this->pdf->y += $this->majorTopMargin;
            $this->pdf->SetX($this->leftMargin);
            $this->blue();
            $this->pdf->SetFont('Roboto', 'bold', $this->majorFontSize);
            $this->pdf->SetAlpha(1);

            $this->pdf->Cell($this->majorWidth, $this->majorHeight, utf8_decode(ucfirst($majorResult->getName())), 0, 1);

            $this->generateBranches($majorResult);

            if(strtoupper($majorResult->getName()) == 'NEDERLANDS') {
                $this->pdf->AddPage();
            }
        }
    }

    /**
     * @param $majorResult
     */
    private function generateBranches(MajorResult $majorResult)
    {
        /** @var BranchResult $branchResult */
        foreach ($majorResult->getBranchResults() as $branchResult) {
            $this->pdf->SetX($this->leftMargin);
            $this->pdf->y += $this->branchTopMargin;
            $this->blue();
            $this->pdf->SetAlpha(.84);

            $history = $branchResult->getHistory();
            $iacs = $branchResult->getIacs();

            $this->generatePointResults($history, $branchResult);

            $this->generateIac($iacs, $branchResult);

            //BOTTOM LINE BENEATH BRANCH
            $this->pdf->y += $this->branchBottomMargin;
            $this->blue();
            $this->pdf->SetAlpha(.54);
            $this->pdf->SetDrawColor(self::BLUE[0], self::BLUE[1], self::BLUE[2]);
            $this->pdf->Line($this->leftMargin, $this->pdf->y, $this->pdf->pageWidth() - 20, $this->pdf->y);
        }
    }

    /**
     * @param $history
     * @param $branchResult
     */
    private function generatePointResults(ArrayCollection $history, BranchResult $branchResult)
    {
        if (count($history) > 0) {
            $this->pdf->SetFont('Roboto', '', $this->branchFontSize);

            //MAKE TITLE
            if (strtoupper($branchResult->getName()) != 'PERMANENTE EVALUATIE') {
                $this->pdf->Cell($this->branchWidth, $this->branchHeight, utf8_decode(ucfirst($branchResult->getName())));
            }

            $this->makePoint($history->get(0), $this->pdf->GetY());
            $this->makeGraph($branchResult->getHistory());
        }
    }
    private function makePoint(RangeResult $rangeResult, $top)
    {
        $this->pdf->SetX($this->pointLeftMargin);

        $this->pdf->SetFont('Roboto', '', $this->subPointFontSize);
        $this->pdf->SetAlpha(.84);

        //PERMANENT
        if ($rangeResult->getPermanent()) {
            $text = 'permanent: ' . $rangeResult->getPermanent() . '/' . $rangeResult->getMax();
            $this->pdf->Cell($this->subPointWidth, $this->subPointHeight, $text, 0, 0, 'R');
        }
        $this->pdf->y += $this->subPointHeight;


        //FINAL
        if ($rangeResult->getFinal() && $rangeResult->getPermanent()) {
            $this->pdf->SetX($this->pointLeftMargin);
            $text = 'eindtoets: ' . $rangeResult->getFinal() . '/' . $rangeResult->getMax();
            $this->pdf->Cell($this->subPointWidth, $this->subPointHeight, $text, 0, 0, 'R');
        }
        $this->pdf->y += $this->subPointHeight;


        //TOTAL
        $this->pdf->SetXY($this->totalPointLeftMargin, $top - ($this->subPointHeight / 2));

        $this->pdf->SetFontSize($this->totalPointFontSize);
        $this->pdf->SetAlpha(.84);
        $this->pdf->Cell($this->totalPointWidth, $this->totalPointHeight, $rangeResult->getTotal() . '/' . $rangeResult->getMax(), 0, 1, 'R');

        //REDICODI
        if (count($rangeResult->getRedicodi()) > 0) {
            $this->pdf->SetXY($this->leftMargin + 10, $top + $this->branchHeight);
            foreach ($rangeResult->getRedicodi() as $key => $value) {
                if ($value >= $rangeResult->getEvCount() / 2) {
                    $this->pdf->SetFont('NotosIcon', '', $this->redicodiFontSize);
                    $icon = $this->fontmap[$key];
                    $this->pdf->Cell($this->redicodiHeight, $this->redicodiHeight, $icon, 0, 0);
                }
            }
            $this->pdf->Ln();
        } else {
            $this->pdf->y += $this->redicodiHeight;
        }
    }

    private function makeGraph(ArrayCollection $history)
    {
        if (count($history) > 1) {
            $arr = [];
            $arr[] = [
                'color' => self::BLUE,
                'data' => []
            ];
            $data = [];

            /** @var RangeResult $rangeResult */
            foreach ($history as $rangeResult) {
                $percent = ($rangeResult->getTotal() / $rangeResult->getMax()) * 100;
                $data[] = ['key' => $rangeResult->getRange()->getStart()->format('Y-m-d'), 'value' => $percent];
            }
            $arr[0]['data'] = $data;
            $this->pdf->SetX(162);
            $this->pdf->SetAlpha(.84);

            $this->pdf->LineChart($this->pdf->x, $this->pdf->y - 15, 35, 16, null, $arr);
        }
    }

    /**
     * @param $iacs
     * @param $branchResult
     */
    private function generateIac(ArrayCollection $iacs, BranchResult $branchResult)
    {
        if (count($iacs) > 0) {

            //MAKE TITLE
            $this->pdf->SetFont('Roboto', 'bold', $this->branchFontSize);
            $this->pdf->Cell($this->iacTextWidth, $this->branchHeight, utf8_decode(ucfirst($branchResult->getName())), 0, 0);

            $this->pdf->SetFont('Roboto', 'bold', $this->iacCommentFontSize);
            $this->pdf->Cell($this->iacIconWidth, $this->branchHeight, $this->iacAchievedText, 0, 0, 'C');
            $this->pdf->Cell($this->iacIconWidth, $this->branchHeight, $this->iacPracticeText, 0, 0, 'C');
            $this->pdf->Cell($this->iacCommentWidth, $this->branchHeight, $this->iacCommentText, 0, 1);

            $this->pdf->y += $this->branchTopMargin;
            $this->pdf->SetLineWidth(0.5);
            $this->pdf->Line($this->leftMargin, $this->pdf->y, $this->pdf->pageWidth() - $this->leftMargin, $this->pdf->y);
            $this->pdf->SetLineWidth(0.1);

            foreach ($iacs as $iac) {
                $this->generateIacGoals($iac);
            }
        }
    }

    private function generateIacGoals(IacResult $iac)
    {

        /** @var IacGoalResult $goal */
        foreach ($iac->getGoals() as $goal) {
            $this->pdf->SetX($this->leftMargin);
            $this->pdf->y += $this->iacTextTopMargin;
            $this->pdf->SetFont('Roboto', '', $this->iacTextFontSize);
            $this->pdf->SetAlpha(0.84);
            $y1 = $this->pdf->GetY();
            $x = $this->pdf->x;
            $mc = new Multicell($this->pdf);
            $mc->setStyle( "p", 'roboto', "", $this->iacCommentFontSize, implode(',', self::BLUE));

            $mc->multiCell($this->iacTextWidth, $this->iacTextHeight,'<p>' . utf8_decode($goal->getText() . '</p>'), 0);

            $y2 = $this->pdf->GetY();

            $this->pdf->SetXY($x + $this->iacTextWidth, $y1);
            $this->pdf->SetFont('NotosIcon', '', $this->iacIconFontSize);

            $achieved = $goal->isAchieved() ? $this->fontmap['CHECK'] : '';
            $this->pdf->Cell($this->iacIconWidth, $this->iacTextHeight, $achieved, 0, 0, 'C');

            $practice = $goal->isPractice() ? $this->fontmap['CHECK'] : '';
            $this->pdf->Cell($this->iacIconWidth, $this->iacTextHeight, $practice, 0, 0, 'C');

            $this->pdf->SetFont('Roboto', '', $this->iacCommentFontSize);
            $this->pdf->SetXY($x + $this->iacTextWidth + ($this->iacIconWidth * 2), $y1);
            $mc->multiCell($this->iacCommentWidth, $this->iacTextHeight, utf8_decode($goal->getComment()), 0);

            $y3 = $this->pdf->GetY();

            $toY = $y1 > $y2 ?
                        ($y3 > $y1 ? $y3 : $y1) :
                        ($y3 > $y2 ? $y3 : $y2);
            $this->pdf->SetY($toY + $this->iacTextTopMargin);
            $this->pdf->SetAlpha(0.54);
            $this->pdf->SetDash(0.5, 1);
            $this->pdf->SetDrawColor(self::BLUE[0], self::BLUE[1], self::BLUE[2]);
            $this->pdf->Line($this->leftMargin, $this->pdf->y, $this->pdf->pageWidth() - $this->leftMargin, $this->pdf->y);
            $this->pdf->SetDash(); //reset
        }
    }

    private function cmp( $a, $b ) {
        if(  $a == $b ){ return 0 ; }
        return ($a < $b) ? -1 : 1;
    }


    public function Header(StudentResult $studentResult)
    {
        $this->orange();

        $this->pdf->y = 10;

        $this->pdf->SetAlpha(.12);
        $this->pdf->SetFontSize(70);
        $this->pdf->Cell(0, 30, $studentResult->getGroup());

        $this->pdf->SetFontSize(20);
        $this->pdf->SetAlpha(1);
        $this->pdf->SetXY(20, 0);
        $this->blue();

        $this->pdf->Cell(($this->pdf->pageWidth() - 20) / 2, 30, utf8_decode($studentResult->getTitular()));

        $this->orange();
        $fn = utf8_decode($studentResult->getFirstName());
        $ln = utf8_decode($studentResult->getLastName());
        $this->pdf->Cell(($this->pdf->pageWidth() - 20) / 2 - 10, 30, $fn . '|' . $ln, 0, 1, 'R');

        $this->pdf->SetAlpha(1);
        $this->orange();
        $this->pdf->SetX($this->leftMargin);

        $this->pdf->Cell(0, 10, 'Dit zijn mijn leervorderingen', 0, 1);
        $this->pdf->SetDrawColor(self::ORANGE[0], self::ORANGE[1], self::ORANGE[2]);
        $this->pdf->Line($this->leftMargin, $this->pdf->y, $this->pdf->pageWidth() - $this->pdf->x, $this->pdf->y);
    }

    public function Footer(StudentResult $studentResult)
    {
        $this->pdf->SetY(-25);
        $this->pdf->SetFont('Roboto', 'bold', 18);
        $this->orange();

        $this->pdf->SetAlpha(.54);

        $formatter = new IntlDateFormatter('nl_BE', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
        $formatter->setPattern('MMMM');
        $start = $formatter->format($this->report->getRange()->getStart());
        $end = $formatter->format($this->report->getRange()->getEnd());
        $this->pdf->Cell(0, 7, $start . '-' . $end, 0, 1);

        $this->pdf->SetFontSize(40);
        $formatter->setPattern('YYYY');
        $year = $formatter->format($this->report->getRange()->getEnd());
        $this->pdf->Cell(0, 10, $year, 0, 1);
    }

#region COLORS
    private function blue()
    {
        call_user_func_array([$this->pdf, 'SetTextColor'], self::BLUE);
    }

    private function orange()
    {
        call_user_func_array([$this->pdf, 'SetTextColor'], self::ORANGE);
    }

#endregion

    private function makeFrontPage(StudentResult $student)
    {
        $this->pdf->AddPage();
        $this->pdf->SetFont('Roboto', 'bold', 18);
        $vbsde = 'VBS De';
        $wVBS = $this->pdf->GetStringWidth($vbsde) + 2;

        $klimtoren = 'Klimtoren';
        $wKl = $this->pdf->GetStringWidth($klimtoren);

        $this->blue();
        $this->pdf->Cell($wVBS, 5, $vbsde);

        $this->orange();
        $this->pdf->Cell($wKl, 5, $klimtoren, 0, 1);

        $this->pdf->SetY(-42);
        $this->blue();
        $this->pdf->SetFontSize(35);
        $this->pdf->Cell(0, 9, 'EVALUATIES', 0, 1);

        $this->orange();
        $this->pdf->SetFontSize(50);
        $this->pdf->ShadowFittCell(0, 25, utf8_decode($student->getDisplayName()), 0, 1, '', false, '', self::BLUE, 1, .12);
    }
}