<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 25/11/16
 * Time: 20:57
 */

namespace App\Domain\Services\Pdf;


use App\Domain\Model\Education\Branch;
use App\Domain\Model\Reporting\BranchResult;
use App\Domain\Model\Reporting\IacGoalResult;
use App\Domain\Model\Reporting\MajorResult;
use App\Domain\Model\Reporting\RangeResult;
use App\Domain\Model\Reporting\Report;
use App\Domain\Model\Reporting\StudentResult;
use App\Pdf\NtPdf\Listener;
use App\Pdf\NtPdf\Multicell;
use App\Pdf\Pdf;
use App\Pdf\PdfTable;
use IntlDateFormatter;

class PdfReport
{
    /** @var PdfTable */
    private $resultsTable;
    /** @var PdfTable */
    private $iacTable;
    /** @var  Report */
    private $report;

    /* ***************************************************
     * styles
     * **************************************************/
    private $leftMargin = 20;

    private $iacTextWidth = 80;
    private $iacCommentWidth = 50;
    private $iacIconWidth = 20;

    /* ***************************************************
     * C'tor
     * **************************************************/
    public function __construct(Report $report)
    {
        setlocale(LC_ALL, 'nl_BE');

        $this->report = $report;

        $this->pdf = new Pdf();

        $this->initFonts();

        $this->pdf->SetAutoPageBreak(false, 45);

        $this->build();
    }

    public function build()
    {
        /** @var StudentResult $result */
        foreach ($this->report->getStudentResults() as $result) {
            $this->pdf->HideHeader();
            $this->pdf->HideFooter();

            $this->makeFrontPage($result);

            $this->pdf->ShowHeader();

            $this->pdf->header = $this->StudentHeader();
            $this->pdf->footer = $this->StudentFooter();

            $this->pdf->student = $result;

            $this->pdf->AddPage();

            $this->pdf->ShowFooter();

            $mc = new Multicell($this->pdf);
            $this->initMulticell($mc);
            $mc->multiCell(0, 100, $result->getFeedback());

            $this->pdf->AddPage();


            $this->makeResultsTable($result);

        }
        return $this;
    }

    public function StudentHeader()
    {
        return function (StudentResult $studentResult) {
            $tit = utf8_decode($studentResult->getTitular());
            $fn = utf8_decode($studentResult->getFirstName());
            $ln = utf8_decode($studentResult->getLastName());


            $headerTable = new PdfTable($this->pdf);
            $this->initTable($headerTable);

            $headerTable->initialize([85, 85]);
            $row = [
                ['TEXT' => '<tn>' . $tit . '</tn>', 'BORDER_TYPE' => 0, 'PADDING_BOTTOM' => 10],
                ['TEXT' => '<fn>' . $fn . ' |</fn> <ln>' . $ln . '</ln>', 'TEXT_ALIGN' => 'R', 'BORDER_TYPE' => 0, 'PADDING_BOTTOM' => 10]
            ];
            $headerTable->addRow($row);


            $title = [
                ['TEXT' => '<h2>Dit zijn mijn leervorderingen</h2>',
                    'COLSPAN' => 2,
                    'BORDER_TYPE' => 'B',
                    'BORDER_COLOR' => Colors::ORANGE,
                    'BORDER_SIZE' => .5,
                    'PADDING_BOTTOM' => 3]
            ];
            $headerTable->addRow($title);
            $headerTable->close();

            $this->orange();
            $this->pdf->SetXY(10, 0);
            $this->pdf->SetAlpha(.12);
            $this->pdf->SetFont('roboto', 'b', 70);
            $this->pdf->Cell(0, 50, $studentResult->getGroup());
            $this->pdf->SetXY($this->leftMargin, 35);
            $this->pdf->SetAlpha(1);
        };
    }

    public function StudentFooter()
    {
        return function (StudentResult $studentResult) {
            $this->pdf->SetY(-35);
            $this->pdf->SetFont('Roboto', 'B', 18);
            $this->orange();

            //NAME
            $this->pdf->SetAlpha(.12);
            $this->pdf->SetFontSize(200);
            $fn = $studentResult->getFirstName();
            $fnW = $this->pdf->GetStringWidth($fn);
            $this->pdf->x = 0 - (($fnW - $this->pdf->pageWidth()) / 2);
            $this->pdf->Cell($fnW, 50, $fn, 0, 0);


            //RANGE
            $this->blue();
            $this->pdf->SetY(-40);
            $this->pdf->SetAlpha(.84);
            $this->pdf->SetFontSize(18);
            $formatter = new IntlDateFormatter('nl_BE', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
            $formatter->setPattern('MMMM');
            $start = $formatter->format($this->report->getRange()->getStart());
            $end = $formatter->format($this->report->getRange()->getEnd());

            $this->pdf->ShadowCell(0, 10, $start . '-' . $end, 0, 1, '', false, '', Colors::BLUE, 1, .12);

            // YEAR
            $this->orange();
            $this->pdf->SetFontSize(70);
            $formatter->setPattern('YYYY');
            $year = $formatter->format($this->report->getRange()->getEnd());
            $this->pdf->ShadowCell(0, 20, $year, 0, 1, '', false, '', Colors::BLUE, 1, .12);
            //$this->pdf->Cell(0, 20, , 0, 1);
        };
    }


    public function output()
    {
        $this->pdf->Output();
    }

    /* ***************************************************
     * Privates
     * **************************************************/
    private function makeResultsTable(StudentResult $studentResult)
    {
        /** @var MajorResult $majorResult */
        foreach ($studentResult->getMajorResults() as $majorResult) {
            //@todo: check if major has any branch history results
            $hasResult = false;
            $hasIacs = false;
            /** @var BranchResult $branchResult */
            foreach ($majorResult->getBranchResults() as $branchResult) {
                if (count($branchResult->getHistory()) > 0) {
                    $hasResult = true;
                }
                if (count($branchResult->getIacs()) > 0) {
                    $hasIacs = true;
                }
                if($branchResult->hasComprehensive() || $branchResult->hasSpoken()) {
                    $hasResult = true;
                }
            }

            $headerMajor = [
                ['TEXT' => '<h3>' . ucfirst(utf8_decode($majorResult->getName())) . '</h3>',
                    'COLSPAN' => 4,
                    'TEXT_ALIGN' => 'L',
                    'PADDING_TOP' => 13,
                    'PADDING_BOTTOM' => 3,
                    'BORDER_TYPE' => 0,
                ]
            ];
            $headerMajorSet = false;


            if ($hasResult) {
                $this->resultsTable = new PdfTable($this->pdf);
                $this->initTable($this->resultsTable);

                $this->resultsTable->initialize([65, 35, 30, 40]);
                $this->resultsTable->addHeader($headerMajor);
                $headerMajorSet = true;

                /** @var BranchResult $branchResult */
                foreach ($majorResult->getBranchResults() as $branchResult) {
                    $row = [[], []];
                    $history = $branchResult->getHistory();

                    //SPOKEN OR COMPREHENSIVE EVALUATION
                    //FOR NOW BOTH CAN NOT BE TRUE
                    //@todo: were to check this?

                    $icon = [];
                    if ($branchResult->hasComprehensive()) {
                        $icon[] = 'o';
                    }
                    if ($branchResult->hasSpoken()) {
                        $icon[] = 'k';
                    }
                    if (count($icon) > 0) {
                        $branch = '<bn>' . utf8_decode(ucfirst($branchResult->getName())) . '</bn>';
                        $row[0] = [
                            'TEXT' => $branch,
                            'PADDING_TOP' => 4,
                            'PADDING_BOTTOM' => 4,
                            'LINE_SIZE' => 4,
                        ];
                        $row[1] = [];
                        $row[2] = [
                            'TEXT' => '<bi>' . implode('</bi>   <bi>', $icon) . '</bi>',
                            'TEXT_ALIGN' => 'R',
                            'PADDING_TOP' => 4,
                            'PADDING_BOTTOM' => 4
                        ];
                        $this->resultsTable->addRow($row);
                    }

                    if (count($history) > 0) {
                        /** @var RangeResult $rangeResult */
                        $rangeResult = $history->get(0);

                        $isPerm = strtoupper($branchResult->getName()) == 'PERMANENTE EVALUATIE';

                        $branch = '<bn>' . utf8_decode(ucfirst($branchResult->getName())) . '</bn>';
                        $row[0] = [
                            'PADDING_TOP' => 4,
                            'PADDING_BOTTOM' => 4,
                            'LINE_SIZE' => 4,
                        ];
                        if (count($rangeResult->getRedicodi()) > 0) {
                            $branch .= "\n\t";
                            $row[0] = [
                                'PADDING_TOP' => 2,
                                'PADDING_BOTTOM' => 2,
                                'LINE_SIZE' => 6,
                            ];
                            foreach ($rangeResult->getRedicodi() as $key => $value) {
                                if ($value >= $rangeResult->getEvCount() / 2) {
                                    $icon = NotosIcon::MAP[$key];
                                    $branch .= '<i>' . $icon . '</i>';
                                }
                            }
                        }
                        $row[0]['TEXT'] = $branch;


                        if (!$isPerm) {
                            $points = [];
                            if ($rangeResult->getPermanent()) {
                                $points[] = 'permanent: ' . $rangeResult->getPermanent() . '/' . $rangeResult->getMax();
                            }
                            if ($rangeResult->getFinal()) {
                                $points[] = 'eindevaluatie: ' . $rangeResult->getFinal() . '/' . $rangeResult->getMax();
                            }


                            $row[1] = [
                                'TEXT' => '<sm>' . implode("\n", $points) . '</sm>',
                                'TEXT_ALIGN' => 'R',
                                'PADDING_TOP' => 4,
                                'PADDING_BOTTOM' => 4
                            ];
                        }

                        $t = $rangeResult->getTotal() . '/' . $rangeResult->getMax();

                        $row[2] = [
                            'TEXT' => $isPerm ? '<perm>' . $t . '</perm>' : '<t>' . $t . '</t>',
                            'TEXT_ALIGN' => 'R',
                            'PADDING_TOP' => 4,
                            'PADDING_BOTTOM' => 4
                        ];

                        $this->resultsTable->addRow($row);
                    }
                }

                $this->resultsTable->close();
                if ($majorResult->getName() == 'Nederlands') {
                    $this->pdf->AddPage();
                }
            }

            if ($hasIacs) {
                if (!$headerMajorSet) {
                    $this->iacTable = new PdfTable($this->pdf);
                    $this->initTable($this->iacTable);

                    $this->iacTable->initialize(array($this->iacTextWidth, $this->iacIconWidth, $this->iacIconWidth, $this->iacCommentWidth));
                    //add the header row
                    $this->iacTable->addHeader($headerMajor);
                    $this->iacTable->close();

                    $headerMajorSet = true;
                }


                $this->iacTable = new PdfTable($this->pdf);
                $this->initTable($this->iacTable);

                $this->iacTable->initialize(array($this->iacTextWidth, $this->iacIconWidth, $this->iacIconWidth, $this->iacCommentWidth));
                //add the header row


                foreach ($branchResult->getIacs() as $iac) {
                    $this->iacTable->setHeaderConfig([
                        'BORDER_COLOR' => Colors::llblue(),
                        'BORDER_WIDTH' => 1,
                        'TEXT_COLOR' => Colors::lblue()
                    ]);
                    $IACHeader = [
                        ['TEXT' => '<h4>Individuele leerlijn</h4>', 'PADDING_TOP' => 10, 'TEXT_ALIGN' => 'L', 'COLSPAN' => 4, 'BORDER_TYPE' => 0]
                    ];
                    $branchHeader = [
                        ['TEXT' => '<bn>' . utf8_decode(ucfirst($branchResult->getName())) . '</bn>', 'TEXT_ALIGN' => 'L'],
                        ['TEXT' => 'Gekend', 'TEXT_SIZE' => 8],
                        ['TEXT' => 'Nog oefenen', 'TEXT_SIZE' => 8],
                        ['TEXT' => 'Opmerkingen', 'TEXT_SIZE' => 8, 'TEXT_ALIGN' => 'L'],
                    ];
                    $this->iacTable->addHeader($IACHeader);
                    $this->iacTable->addHeader($branchHeader);


                    $this->iacTable->setRowConfig([
                        'BORDER_COLOR' => Colors::lllblue(),
                        'BORDER_WIDTH' => .1
                    ]);
                    /** @var IacGoalResult $goal */
                    foreach ($iac->getGoals() as $goal) {
                        //row 1 - add data as Array
                        $aRow = array();
                        $aRow = [
                            ['TEXT' => '<g>' . utf8_decode($goal->getText()) . '</g>', 'TEXT_ALIGN' => 'L'],
                            ['TEXT' => $goal->isAchieved() ? '<i>' . 'n' . '</i>' : '', 'TEXT_ALIGN' => 'C'],
                            ['TEXT' => $goal->isPractice() ? '<i>' . 'n' . '</i>' : '', 'TEXT_ALIGN' => 'C'],
                            ['TEXT' => '<sm>' . utf8_decode($goal->getComment()) . '</sm>', 'TEXT_ALIGN' => 'J']
                        ];

                        //add the data row
                        $this->iacTable->addRow($aRow);
                    }
                }

                //close the table
                $this->iacTable->close();
            }
        }
    }


    private function initFonts()
    {
        foreach (Fonts::ALL as $font) {
            $this->pdf->AddFont($font['name'], $font['style'], $font['file']);
        }
    }

    private function initTable(PdfTable $table)
    {
        $table->setStyle('tn', 'Roboto', '', 15, Colors::str_blue());
        $table->setStyle('fn', 'Roboto', '', 20, Colors::str_blue());
        $table->setStyle('ln', 'Roboto', '', 20, Colors::str_orange());

        $table->setStyle('h2', 'Roboto', 'b', 20, Colors::str_orange());
        $table->setStyle('h3', 'Roboto', 'b', 18, Colors::str_blue());
        $table->setStyle('h4', 'Roboto', 'b', 12, Colors::str_blue());

        $table->setStyle('b', 'Roboto', 'b', 10, Colors::str_blue(), .84);
        $table->setStyle('bn', 'Roboto', '', 12, Colors::str_blue());

        $table->setStyle('p', 'Roboto', '', 10, Colors::str_blue());
        $table->setStyle('t', 'Roboto', '', 12, Colors::str_blue());
        $table->setStyle('perm', 'Roboto', '', 10, Colors::str_blue(), .54);

        $table->setStyle('sm', 'Roboto', '', 8, Colors::str_blue(), .84);
        $table->setStyle('g', 'Roboto', '', 9, Colors::str_blue(), .84);


        $table->setStyle('bi', 'NotosIcon', '', 16, Colors::str_blue(), .84);
        $table->setStyle('i', 'NotosIcon', '', 12, Colors::str_blue(), .84);
    }
    private function initMulticell(Multicell $mc) {
        $mc->setStyle('b', 'Roboto', 'b', 11, Colors::str_blue());
        $mc->setStyle('p', 'Roboto', '', 11, Colors::str_blue());

    }

    private function makeFrontPage(StudentResult $student)
    {
        $this->pdf->AddPage();
        $this->pdf->SetFont('Roboto', 'B', 18);
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
        $this->pdf->ShadowCell(0, 25, utf8_decode($student->getDisplayName()), 0, 1, '', false, '', Colors::BLUE, 1, .12);
    }


    private function blue()
    {
        call_user_func_array([$this->pdf, 'SetTextColor'], Colors::BLUE);
    }

    private function orange()
    {
        call_user_func_array([$this->pdf, 'SetTextColor'], Colors::ORANGE);
    }

}