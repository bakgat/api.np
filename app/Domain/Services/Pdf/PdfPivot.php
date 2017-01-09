<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 3/01/17
 * Time: 09:34
 */

namespace App\Domain\Services\Pdf;


use App\Domain\Model\Reporting\BranchHeader;
use App\Domain\Model\Reporting\BranchResult;
use App\Domain\Model\Reporting\MajorHeader;
use App\Domain\Model\Reporting\MajorResult;
use App\Domain\Model\Reporting\RangeResult;
use App\Domain\Model\Reporting\Report;
use App\Domain\Model\Reporting\ReportHeader;
use App\Domain\Model\Reporting\StudentResult;
use App\Pdf\Pdf;
use App\Pdf\PdfTable;

class PdfPivot
{
    /** @var Pdf */
    private $pdf;
    /** @var  PdfTable */
    private $resultsTable;
    /** @var  Report */
    private $report;

    public function __construct(Report $report)
    {
        setlocale(LC_ALL, 'nl_BE');

        $this->report = $report;

        $this->pdf = new Pdf('L');

        $this->initFonts();

        $this->build();
    }

    private function build()
    {
        $this->pdf->AddPage();
        $startX = 20;
        $startY = 20;
        $colIds = [];

        $this->pdf->SetXY($startX, $startY);

        $this->pdf->SetFont('Roboto', '', 10);

        $table = new PdfTable($this->pdf);
        $header[0] = ['TEXT' => ''];

        /** @var MajorHeader $majorHeader */
        foreach ($this->report->getHeader()->getMajorHeaders() as $majorHeader) {
            /** @var BranchHeader $branchHeader */
            foreach ($majorHeader->getBranchHeaders() as $branchHeader) {
                $header[] = ['TEXT' => $branchHeader->getName(), 'ROTATE' => 90, 'MARGIN' => 0, 'TEXT_SIZE' => 8, 'TEXT_ALIGN' => 'C', 'VERTICAL_ALIGN' => 'B',];
                $colIds[] = $branchHeader->getId();
            }
        }
        $colW = ($this->pdf->pageWidth() - ($this->pdf->x * 2)) / (count($colIds) + 2);
        $cols = [$colW * 2];
        $cols = array_merge($cols, array_fill(1, count($colIds) + 2, $colW));
        $table->initialize($cols);
        $table->addHeader($header);


        /** @var StudentResult $studentResult */
        foreach ($this->report->getStudentResults() as $studentResult) {
            $count = 0;
            $row[$count++] = ['TEXT' => utf8_decode($studentResult->getDisplayName()), 'TEXT_SIZE' => 8];

            /** @var MajorResult $majorResult */
            foreach ($studentResult->getMajorResults() as $majorResult) {
                /** @var BranchResult $branchResult */
                foreach ($majorResult->getBranchResults() as $branchResult) {
                    /** @var RangeResult $rr */
                    $rr = $branchResult->getHistory()->first();
                    if ($rr) {
                        $idx = array_index_of($branchResult->getId(), $colIds);
                        if ($idx > -1) {
                            $results = [];
                            if ($rr->getPermanent() && $rr->getFinal()) {
                                $results[] = '<p>' . $rr->getPermanent() . '</p>';
                                $results[] = '<f>' . $rr->getFinal() . '</f>';
                            }
                            $results[] = '<t>' . $rr->getTotal() . '</t>';
                            $row[$count] = ['TEXT' => implode("\n", $results), 'TEXT_ALIGN' => 'C', 'TEXT_SIZE' => 8];
                        }
                        $count++;
                    }
                }
            }
            $table->addRow($row);
        }

        $table->close();

        return $this;
    }

    public function output($name)
    {
        $this->pdf->Output($name . '.pdf', 'I');
    }

    private function initFonts()
    {
        foreach (Fonts::ALL as $font) {
            $this->pdf->AddFont($font['name'], $font['style'], $font['file']);
        }
    }
}