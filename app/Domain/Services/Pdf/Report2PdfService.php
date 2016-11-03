<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 18/10/16
 * Time: 08:59
 */

namespace App\Domain\Services\Pdf;

use App\Domain\Model\Evaluation\EvaluationRepository;
use App\Domain\Model\Identity\Student;
use App\Domain\Model\Reporting\BranchResult;
use App\Domain\Model\Reporting\MajorResult;
use App\Domain\Model\Reporting\RangeResult;
use App\Domain\Model\Reporting\Report;
use App\Domain\Model\Reporting\StudentResult;
use App\Domain\Model\Time\DateRange;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;


class Report2PdfService
{
    /** @var EvaluationRepository */
    private $evaluationRepo;

    private $pdf;

    const ORANGE = [231, 155, 0];
    const BLUE = [0, 87, 157];

    /** @var Report */
    private $report;

    /** @var bool */
    private $frontPage = false;

    public function __construct(EvaluationRepository $evaluationRepository)
    {
        app('translator')->setLocale('nl_BE');
        $this->evaluationRepo = $evaluationRepository;
        $this->pdf = new Ntpdf();
        $this->pdf->AddFont('Roboto', '', 'Roboto-Regular.php');
        $this->pdf->AddFont('Roboto', 'bold', 'Roboto-Bold.php');
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
            $this->pdf->AcceptPageBreak();
            /** @var MajorResult $majorResult */
            foreach ($result->getMajorResults() as $majorResult) {
                $this->pdf->SetX(42);
                $this->blue();
                $this->pdf->SetFont('Roboto', 'bold', 17);
                $this->pdf->SetAlpha(1);
                $this->pdf->Cell(0, 15, utf8_decode(ucfirst($majorResult->getName())), 0, 1);
                /** @var BranchResult $branchResult */
                foreach ($majorResult->getBranchResults() as $branchResult) {
                    $this->pdf->SetX(42);
                    $this->blue();
                    $this->pdf->SetFont('Roboto', '', 12);
                    $this->pdf->SetAlpha(.84);
                    $this->pdf->y += 3;
                    $this->pdf->Cell(50, 10, utf8_decode(ucfirst($branchResult->getName())));

                    $history = $branchResult->getHistory();

                    $this->makePoint($history->get(0));
                    $this->makeGraph($branchResult->getHistory());

                    $this->pdf->SetDrawColor(self::BLUE[0], self::BLUE[1], self::BLUE[2]);
                    $this->pdf->SetAlpha(.54);
                    $this->pdf->y += 3;
                    $this->pdf->Line(42, $this->pdf->y, $this->pdf->pageWidth() - 20, $this->pdf->y);
                }
            }

        }

        return $this->pdf->Output();
    }

    private function makePoint(RangeResult $rangeResult)
    {
        $this->pdf->SetX(92);

        $this->pdf->SetFont('Roboto', '', 9);
        $this->pdf->SetAlpha(.54);

        if ($rangeResult->getPermanent()) {
            $text = 'permanent: ' . $rangeResult->getPermanent() . '/' . $rangeResult->getMax();
            $this->pdf->y += 0;
            $this->pdf->Cell(0, 5, $text, 0, 1);
        } else {
            $this->pdf->y += 5;
        }

        if ($rangeResult->getFinal() && $rangeResult->getPermanent()) {
            $this->pdf->SetX(92);
            $text = 'eindtoets: ' . $rangeResult->getFinal() . '/' . $rangeResult->getMax();
            $this->pdf->Cell(0, 5, $text, 0, 1);
        } else {
            $this->pdf->y += 5;
        }

        $this->pdf->SetX(132);
        $this->pdf->y -= 10;
        $this->pdf->SetFontSize(12);
        $this->pdf->SetAlpha(.84);
        $this->pdf->Cell(0, 10, $rangeResult->getTotal() . '/' . $rangeResult->getMax(), 0, 1);
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

    public function Header(StudentResult $studentResult)
    {
        $this->orange();

        $this->pdf->y = 10;

        $this->pdf->SetAlpha(.12);
        $this->pdf->SetFontSize(70);
        $this->pdf->Cell(0, 30, $studentResult->getActiveGroupName());

        $this->pdf->SetFontSize(20);
        $this->pdf->SetAlpha(1);
        $this->pdf->SetXY(20, 0);
        $this->blue();
        $this->pdf->Cell(($this->pdf->pageWidth() - 20) / 2, 30, utf8_decode($studentResult->getTitular()));

        $this->orange();
        $fn =  utf8_decode($studentResult->getFirstName());
        $ln = utf8_decode($studentResult->getLastName());
        $this->pdf->Cell(($this->pdf->pageWidth() - 20) / 2 - 10, 30, $fn . '|' . $ln, 0, 1, 'R');

        $this->pdf->SetAlpha(1);
        $this->orange();
        $this->pdf->SetX(42);

        $this->pdf->Cell(0, 10, 'Dit zijn mijn leervorderingen', 0, 1);
        $this->pdf->SetDrawColor(self::ORANGE[0], self::ORANGE[1], self::ORANGE[2]);
        $this->pdf->Line($this->pdf->x + 32, $this->pdf->y, $this->pdf->pageWidth() - $this->pdf->x, $this->pdf->y);
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