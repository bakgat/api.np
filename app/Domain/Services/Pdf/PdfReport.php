<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 25/11/16
 * Time: 20:57
 */

namespace App\Domain\Services\Pdf;

use App\Domain\Model\Education\Branch;
use App\Domain\Model\Education\Redicodi;
use App\Domain\Model\Reporting\BranchResult;
use App\Domain\Model\Reporting\IacGoalResult;
use App\Domain\Model\Reporting\MajorResult;
use App\Domain\Model\Reporting\McResult;
use App\Domain\Model\Reporting\RangeResult;
use App\Domain\Model\Reporting\Report;
use App\Domain\Model\Reporting\StudentResult;
use App\Pdf\NtPdf\Listener;
use App\Pdf\NtPdf\Multicell;
use App\Pdf\Pdf;
use App\Pdf\PdfTable;
use App\Support\Encoding;
use Doctrine\Common\Collections\ArrayCollection;
use IntlDateFormatter;

/**
 * @todo: all variables in top as class cfields !
 * Class PdfReport
 * @package App\Domain\Services\Pdf
 */
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

    private $rowPadding = 3;
    private $headerPaddingTop = 8;

    /* ***************************************************
     * C'tor
     * **************************************************/
    public function __construct(Report $report)
    {
        setlocale(LC_ALL, 'nl_BE');

        $this->report = $report;

        $this->pdf = new Pdf();

        $this->initFonts();

        $this->build();
    }

    public function build()
    {
        /** @var StudentResult $result */
        foreach ($this->report->getStudentResults() as $result) {

            $this->pdf->SetAutoPageBreak(false, 45);
            $this->pdf->HideHeader();
            $this->pdf->HideFooter();

            if ($this->report->hasFrontpage()) {
                $this->makeFrontPage($result);
            }

            $this->pdf->ShowHeader();

            $this->pdf->header = $this->StudentHeader(true);

            $this->pdf->footer = $this->StudentFooter();

            $this->pdf->student = $result;

            $this->pdf->AddPage();

            $this->pdf->ShowFooter();

            $this->makeFirstPage($result);

            $this->pdf->header = $this->StudentHeader(false);

            $this->pdf->AddPage();

            $this->makeResultsTable($result);

            $this->makeSignature();

            if ($this->pdf->PageNo() % 2 != 0) {
                $this->pdf->HideHeader();
                $this->pdf->AddPage();
            }

        }
        return $this;
    }

    public function StudentHeader($hideTitle = false)
    {
        return function (StudentResult $studentResult) use ($hideTitle) {
            $prTit = $studentResult->getTitularGender() == 'F' ? 'juf ' : 'meester ';
            $tit = utf8_decode($studentResult->getTitular());
            $fn = utf8_decode($studentResult->getFirstName());
            $ln = utf8_decode($studentResult->getLastName());


            $headerTable = new PdfTable($this->pdf);
            $this->initTable($headerTable);

            $headerTable->initialize([85, 85]);
            $row = [
                ['TEXT' => '<ptn>' . $prTit . ' | </ptn><tn>' . $tit . '</tn>', 'BORDER_TYPE' => 0, 'PADDING_BOTTOM' => 10],
                ['TEXT' => '<fn>' . $fn . ' | </fn> <ln>' . $ln . '</ln>', 'TEXT_ALIGN' => 'R', 'BORDER_TYPE' => 0, 'PADDING_BOTTOM' => 10]
            ];
            $headerTable->addRow($row);

            if (!$hideTitle) {
                $title = [
                    ['TEXT' => '<h2>Dit zijn mijn leervorderingen</h2>',
                        'COLSPAN' => 2,
                        'BORDER_TYPE' => 'B',
                        'BORDER_COLOR' => Colors::ORANGE,
                        'BORDER_SIZE' => .5,
                        'PADDING_BOTTOM' => 3]
                ];
                $headerTable->addRow($title);
            }
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

            $this->orange();
            $this->pdf->SetY(-35);
            $this->pdf->SetFont('Roboto', 'B', 18);

            //NAME
            $this->pdf->SetAlpha(.12);
            $this->pdf->SetFontSize(200);
            $fn = utf8_decode($studentResult->getFirstName());
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

            $this->pdf->Cell(0, 10, $start . '-' . $end, 0, 1); //, '', false, '', Colors::BLUE, 1, .12);

            // YEAR
            $this->orange();
            $this->pdf->SetFontSize(70);
            $formatter->setPattern('YYYY');
            $year = $formatter->format($this->report->getRange()->getEnd());
            $this->pdf->Cell(0, 20, $year, 0, 1); //, '', false, '', Colors::BLUE, 1, .12);
            //$this->pdf->Cell(0, 20, , 0, 1);
        };
    }


    public function output($name)
    {
        $this->pdf->Output($name . '.pdf', 'I');
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

                if ($branchResult->hasComprehensive() || $branchResult->hasSpoken()) {
                    $hasResult = true;
                }
            }

            $headerMajor = [
                ['TEXT' => '<h3>' . ucfirst(utf8_decode($majorResult->getName())) . '</h3>',
                    'COLSPAN' => 4,
                    'TEXT_ALIGN' => 'L',
                    'PADDING_TOP' => $this->headerPaddingTop,
                    'PADDING_BOTTOM' => $this->rowPadding,
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

                $iterator = $majorResult->getBranchResults()->getIterator();

                $iterator->uasort(function($a, $b) {
                    /**
                     * @var BranchResult $a
                     * @var BranchResult $b
                     */
                    return $a->getOrder() < $b->getOrder() ? -1 : 1;
                });
                $newBranchCollection = new ArrayCollection(iterator_to_array($iterator));
                /** @var BranchResult $branchResult */
                foreach ($newBranchCollection as $branchResult) {

                    $history = $branchResult->getHistory();
                    $branchSet = false;

                    $hasMultiplechoices = false;
                    if (count($branchResult->getMultipleChoices()) > 0) {
                        $hasMultiplechoices = true;
                    }

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
                            'PADDING_TOP' => $this->rowPadding,
                            'PADDING_BOTTOM' => $this->rowPadding,
                            'LINE_SIZE' => 4,
                            'BORDER_TYPE' => $hasMultiplechoices ? 0 : 'B',
                        ];
                        $row[1] = ['BORDER_TYPE' => $hasMultiplechoices ? 0 : 'B',];
                        $row[2] = [
                            'TEXT' => '<bi>' . implode('</bi>   <bi>', $icon) . '</bi>',
                            'TEXT_ALIGN' => 'R',
                            'PADDING_TOP' => $this->rowPadding,
                            'PADDING_BOTTOM' => $this->rowPadding,
                            'BORDER_TYPE' => $hasMultiplechoices ? 0 : 'B',
                        ];
                        $row[3] = [
                            'PADDING_TOP' => $this->rowPadding,
                            'PADDING_BOTTOM' => $this->rowPadding,
                            'BORDER_TYPE' => $hasMultiplechoices ? 0 : 'B',
                        ];
                        $this->resultsTable->addRow($row);
                        $branchSet = true;
                    }
                    if (count($history) > 0) {
                        /** @var RangeResult $rangeResult */
                        $rangeResult = $history->get(0);

                        $isPerm = strtoupper($branchResult->getName()) == 'PERMANENTE EVALUATIE';

                        $branch = '<bn>' . utf8_decode(ucfirst($branchResult->getName())) . '</bn>';

                        $row[0] = [
                            'PADDING_TOP' => $this->rowPadding,
                            'PADDING_BOTTOM' => $this->rowPadding,
                            'LINE_SIZE' => 4,
                            'BORDER_TYPE' => $hasMultiplechoices ? 0 : 'B',
                        ];
                        if (count($rangeResult->getRedicodi()) > 0) {
                            $branch .= "\n\t";
                            $row[0] = [
                                'PADDING_TOP' => $this->rowPadding / 2,
                                'PADDING_BOTTOM' => $this->rowPadding / 2,
                                'LINE_SIZE' => 5,
                                'BORDER_TYPE' => $hasMultiplechoices ? 0 : 'B',
                            ];
                            $icons = [];
                            foreach ($rangeResult->getRedicodi() as $key => $value) {
                                if ($value >= $rangeResult->getEvCount() / 2) {
                                    $icons[] = NotosIcon::MAP[$key];
                                }
                            }
                            if (count($icons) > 0) {
                                $branch .= '<i>' . implode('</i>   <i>', $icons) . '</i>';
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

                            if (count($points) > 1) {
                                $pointText = '<sm>' . implode("\n", $points) . '</sm>';
                            } else {
                                $pointText = '';
                            }
                            $row[1] = [
                                'TEXT' => $pointText,
                                'TEXT_ALIGN' => 'R',
                                'PADDING_TOP' => $this->rowPadding,
                                'PADDING_BOTTOM' => $this->rowPadding,
                                'BORDER_TYPE' => $hasMultiplechoices ? 0 : 'B',
                            ];
                        }

                        $t = $rangeResult->getTotal() . '/' . $rangeResult->getMax();

                        $row[2] = [
                            'TEXT' => $t,
                            'TEXT_ALIGN' => 'R',
                            'PADDING_TOP' => $this->rowPadding,
                            'PADDING_BOTTOM' => $this->rowPadding,
                            'BORDER_TYPE' => $hasMultiplechoices ? 0 : 'B',
                        ];

                        $row[3] = [
                            'TEXT' => '',
                            'BORDER_TYPE' => $hasMultiplechoices ? 0 : 'B',
                        ];

                        $this->resultsTable->addRow($row);

                        $branchSet = true;
                    }

                    if ($hasMultiplechoices) {
                        if (!$branchSet) {
                            $branch = '<bn>' . utf8_decode(ucfirst($branchResult->getName())) . '</bn>';

                            $row[0] = [
                                'TEXT' => $branch,
                                'COLSPAN' => 4,
                                'PADDING_TOP' => $this->rowPadding,
                                'PADDING_BOTTOM' => $this->rowPadding,
                                'LINE_SIZE' => 4,
                                'BORDER_TYPE' => 0
                            ];
                            $this->resultsTable->addRow($row);
                        }
                        foreach ($branchResult->getMultipleChoices() as $multipleChoice) {
                            $settings = json_decode($multipleChoice->getSettings());
                            $selected = json_decode($multipleChoice->getSelected());
                            if (!is_array($selected)) {
                                $selected = [$selected];
                            };

                            if ($selected) {
                                $printOther = isset($settings->printOthers) ? $settings->printOthers : false;
                                $selectedStyle = [];
                                $notSelectedStyle = [];
                                switch ($settings->selected) {
                                    case 'bold':
                                        $selectedStyle = ['<b>', '</b>'];
                                        break;
                                    case 'green':
                                        $selectedStyle = ['<gr>', '</gr>'];
                                        break;
                                    case 'red':
                                        $selectedStyle = ['<red>', '</red>'];
                                        break;
                                    default:
                                        $selectedStyle = ['<b>', '</b>'];
                                        break;
                                }
                                switch ($settings->notSelected) {
                                    case 'small':
                                        $notSelectedStyle = ['<sm>', '</sm>'];
                                        break;
                                    case 'red':
                                        $notSelectedStyle = ['<red>', '</red>'];
                                        break;
                                    case 'line-through':
                                        $notSelectedStyle = ['<lt>', '</lt>'];
                                        break;
                                    default:
                                        $notSelectedStyle = ['', ''];
                                        break;
                                }


                                $prefix = isset($settings->pre) ? $settings->pre : null;
                                $suffix = isset($settings->post) ? $settings->post : null;

                                $mc = "\t" . ($prefix ? $prefix . ' ' : '');
                                $optResults = [];
                                if ($printOther) {
                                    $options = $settings->options;
                                    foreach ($options as $option) {
                                        if (in_array($option, $selected)) {
                                            $aOpt = $selectedStyle;
                                            array_splice($aOpt, 1, 0, $option);
                                            $optResults[] = implode('', $aOpt);
                                        } else {
                                            $aOpt = $notSelectedStyle;
                                            array_splice($aOpt, 1, 0, $option);
                                            $optResults[] = implode('', $aOpt);
                                        }
                                    }
                                } else {
                                    $options = $settings->options;
                                    foreach ($options as $option) {
                                        if (in_array($option, $selected)) {
                                            $aOpt = $selectedStyle;
                                            array_splice($aOpt, 1, 0, $option);
                                            $optResults[] = implode('', $aOpt);
                                        }
                                    }
                                }
                                $separator = isset($settings->type) && $settings->type == 'mc' ? "\n\t" : ", ";
                                $mc .= implode($separator, $optResults);
                                $mc .= $suffix ? ' ' . $suffix : '';

                                $row = [
                                    ['TEXT' => $mc,
                                        'COLSPAN' => 4]
                                ];
                                $this->resultsTable->addRow($row);
                            }
                        }
                    }
                }

                $this->resultsTable->close();
                if ($majorResult->getName() == 'Nederlands') {
                    $this->pdf->AddPage();
                }
            }

            if ($hasIacs) {

                /** @var BranchResult $branchResult */
                foreach ($majorResult->getBranchResults() as $branchResult) {
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
                            ['TEXT' => '<h4>Individuele leerlijn</h4>', 'PADDING_TOP' => $this->headerPaddingTop, 'TEXT_ALIGN' => 'L', 'COLSPAN' => 4, 'BORDER_TYPE' => 0]
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

                //new page after iac listings
                $this->pdf->AddPage();
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
        $table->setStyle('tn', 'Roboto', '', 13, Colors::str_blue());
        $table->setStyle('ptn', 'Roboto', '', 13, Colors::str_orange());
        $table->setStyle('fn', 'Roboto', '', 16, Colors::str_blue());
        $table->setStyle('ln', 'Roboto', '', 16, Colors::str_orange());

        $table->setStyle('h2', 'Roboto', 'b', 20, Colors::str_orange());
        $table->setStyle('h3', 'Roboto', 'b', 18, Colors::str_blue());
        $table->setStyle('h4', 'Roboto', 'b', 12, Colors::str_blue());

        $table->setStyle('b', 'Roboto', 'b', 11, Colors::str_blue());
        $table->setStyle('red', 'Roboto', 'b', 10, Colors::str_red());
        $table->setStyle('gr', 'Roboto', 'b', 10, Colors::str_green());

        $table->setStyle('bn', 'Roboto', '', 12, Colors::str_blue());

        $table->setStyle('p', 'Roboto', '', 10, Colors::str_blue());
        $table->setStyle('t', 'Roboto', '', 12, Colors::str_blue());
        $table->setStyle('perm', 'Roboto', '', 10, Colors::str_blue(), .54);

        $table->setStyle('sm', 'Roboto', '', 8, Colors::str_blue(), .84);
        $table->setStyle('g', 'Roboto', '', 9, Colors::str_blue(), .84);


        $table->setStyle('bi', 'NotosIcon', '', 16, Colors::str_blue(), .84);
        $table->setStyle('i', 'NotosIcon', '', 10, Colors::str_blue(), .84);
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

        $this->pdf->SetY(-40);
        $this->blue();
        $this->pdf->SetFontSize(30);
        $this->pdf->Cell(0, 7, 'EVALUATIES', 0, 1);

        $this->orange();
        $this->pdf->SetFontSize(40);
        $this->pdf->Cell(0, 20, utf8_decode($student->getDisplayName()), 0, 1); //, '', false, '', Colors::BLUE, 1, .12);

        $this->makeDescriptionPage();
    }

    private function makeDescriptionPage()
    {
        $this->pdf->SetAutoPageBreak(false, 5);
        $leftMargin = $this->pdf->x;

        $w = $this->pdf->pageWidth() - ($this->pdf->x * 2);
        $titleHeight = 13;
        $explLineHeight = 5;
        $graphsBgHeight = 35;

        $this->pdf->AddPage();

        /* ***************************************************
         * COLORS
         * **************************************************/
        $this->pdf->SetFont('Roboto', 'B', 20);
        $this->blue();
        $t1 = 'Hoe zie ik mezelf?';

        $this->pdf->MultiCell($w, $titleHeight, $t1, 0, 1);

        $this->pdf->x = $leftMargin + 10;
        $this->pdf->SetAlpha(.84);
        $this->pdf->SetTextColor(0);
        $this->pdf->SetFont('RobotoThin', '', 10);
        $c1 = utf8_decode(file_get_contents(resource_path('report_content/expl_colors.tmpl')));
        $this->pdf->MultiCell(0, $explLineHeight, $c1, 0, 1);


        $this->pdf->SetAlpha(1);
        $table = new PdfTable($this->pdf);
        $columnWidth = $w / 3;
        $table->initialize([$columnWidth, $columnWidth, $columnWidth]);

        $table->setRowConfig([
            'BORDER_TYPE' => 0,
            'TEXT_FONT' => 'Roboto',
            'TEXT_TYPE' => 'b',
            'FONT_SIZE' => 12,
            'PADDING_TOP' => 1,
            'PADDING_BOTTOM' => 1
        ]);

        $this->pdf->SetFont('Roboto', 'b', 12);

        $row1 = [
            ['TEXT' => 'Leerjaar 1', 'TEXT_COLOR' => Colors::BLUE],
            ['TEXT' => 'Leerjaar 2', 'TEXT_COLOR' => Colors::GREEN],
            ['TEXT' => 'Leerjaar 3', 'TEXT_COLOR' => Colors::PINK]
        ];
        $row2 = [
            ['TEXT' => 'Leerjaar 4', 'TEXT_COLOR' => Colors::BLACK],
            ['TEXT' => 'Leerjaar 5', 'TEXT_COLOR' => Colors::ORANGE],
            ['TEXT' => 'Leerjaar 6', 'TEXT_COLOR' => Colors::RED]
        ];
        $table->addRow($row1);
        $table->addRow($row2);
        $table->close();


        /* ***************************************************
         * POINT RESULTS
         * **************************************************/
        $this->pdf->SetFont('Roboto', 'B', 20);
        $this->blue();
        $t3 = 'Hoe worden punten weergegeven?';
        $this->pdf->MultiCell($w, $titleHeight, $t3, 0, 1);

        $this->pdf->x = $leftMargin + 10;
        //$this->pdf->y += 5;
        $this->pdf->SetAlpha(.84);
        $this->pdf->SetTextColor(0);
        $this->pdf->SetFont('RobotoThin', '', 10);

        $tbl3 = new PdfTable($this->pdf);
        $tbl3->setRowConfig(
            ['TEXT_COLOR' => Colors::BLACK, 'TEXT_FONT' => 'RobotoThin', 'FONT_SIZE' => 10, 'BORDER_TYPE' => 'B', 'BORDER_COLOR' => [230, 230, 230], 'PADDING_TOP' => 1, 'PADDING_BOTTOM' => 1]
        );

        $tbl3->initialize([30, $w - 50]);
        $r1 = [
            ['TEXT' => 'Dit portfolio bevat heel wat soorten evaluaties. Voor sommige vakken worden punten gegeven.', 'COLSPAN' => 2]
        ];
        $tbl3->addRow($r1);
        $r2 = [
            ['TEXT' => 'Permanent', 'TEXT_TYPE' => 'b'],
            ['TEXT' => 'evaluaties die gedurende het schooljaar worden afgenomen, nadat nieuwe leerstof wordt aangebracht']
        ];
        $r3 = [
            ['TEXT' => 'Eindevaluatie', 'TEXT_TYPE' => 'b'],
            ['TEXT' => 'evaluaties die tijdens een toetsenperiode worden afgenomen. Deze omvatten een groter leerstofgeheel']
        ];
        $r4 = [
            ['TEXT' => 'Totaal', 'TEXT_TYPE' => 'b'],
            ['TEXT' => '40% permanent + 60% eindevaluatie']
        ];
        $tbl3->addRow($r2);
        $tbl3->addRow($r3);
        $tbl3->addRow($r4);

        $tbl3->close();

        $this->pdf->SetAlpha(1);

        /* ***************************************************
         * GRAPHS
         * **************************************************/
        
        $this->pdf->SetFont('Roboto', 'B', 20);
        $this->blue();
        $t2 = 'Wat betekenen de grafieken?';
        $this->pdf->MultiCell($w, $titleHeight, $t2, 0, 1);

        $this->pdf->SetFillColor(Colors::BLUE[0], Colors::BLUE[1], Colors::BLUE[2]);

        $this->pdf->RoundedRect($this->leftMargin, $this->pdf->y, $this->pdf->pageWidth() - ($this->leftMargin * 2), $graphsBgHeight, 5, '1234', 'F');
        $endY = $this->pdf->y + $graphsBgHeight + ($titleHeight / 2);

        $this->pdf->x = $this->leftMargin + 10;
        $this->pdf->y += 5;
        $this->pdf->SetAlpha(.84);
        $this->pdf->SetTextColor(255);
        $this->pdf->SetFont('RobotoThin', '', 10);

        $c2 = utf8_decode(file_get_contents(resource_path('report_content/expl_graphs.tmpl')));
        $this->pdf->MultiCell($this->pdf->pageWidth() - ($this->leftMargin * 2), $explLineHeight, $c2, 0, 1);
        $this->pdf->SetAlpha(1);

        /* ***************************************************
         * Icons
         * **************************************************/
        $this->pdf->y = $endY - 5;
        $this->pdf->SetFont('Roboto', 'B', 20);
        $this->blue();
        $t3 = 'Symbolen';
        $this->pdf->MultiCell($w, $titleHeight, $t3, 0, 1);

        $this->pdf->SetXY(0, $endY + 5);
        $this->pdf->SetAlpha(.84);
        $this->pdf->SetTextColor(0);
        $this->pdf->SetFont('RobotoThin', '', 10);

        $tbl4 = new PdfTable($this->pdf);
        $tbl4->setStyle('i', 'NotosIcon', '', 35, Colors::str_black(), .84);
        $tbl4->setStyle('b', 'RobotoThin', 'b', 10, Colors::str_black(), .84);
        $tbl4->setRowConfig(
            ['TEXT_FONT' => 'RobotoThin', 'FONT_SIZE' => 10, 'TEXT_COLOR' => Colors::BLACK, 'BORDER_TYPE' => 0, 'PADDING_TOP' => 2, 'PADDING_BOTTOM' => 2]
        );
        $tbl4->initialize([15, 70, 15, 70]);
        $r1 = [
            ['TYPE' => 'IMAGE', 'FILE' => resource_path('icons/groups/L1A.png'), 'WIDTH' => 10], ['TEXT' => "<b>Klas</b>\nIn deze klasgroep volgde uw kind les."],
            ['TEXT' => '<i>j</i>', 'TEXT_ALIGN' => 'R'], ['TEXT' => "<b>Vlinderklas</b>\nVoor kleuters en leerlingen 1e leerjaar die naast het gedifferentieerde aanbod tijdelijk meer nood hebben aan uitdaging."],
        ];
        $r2 = [
            ['TEXT' => '<i>g</i>', 'TEXT_ALIGN' => 'R'], ['TEXT' => "<b>Differentiatie naar basis</b>\nUitbreidingsoefeningen worden geschrapt."],
            ['TEXT' => '<i>b</i>', 'TEXT_ALIGN' => 'R'], ['TEXT' => "<b>Minizonnebloemklas</b>\nVoor leerlingen 2e leerjaar die naast het gedifferentieerde aanbod tijdelijk meer nood hebben aan uitdaging."],
        ];
        $r3 = [
            ['TEXT' => '<i>l</i>', 'TEXT_ALIGN' => 'R'], ['TEXT' => "<b>Differentiatie naar uitdaging</b>\nVoor leerlingen die nood hebben aan meer uitdaging."],
            ['TEXT' => '<i>c</i>', 'TEXT_ALIGN' => 'R'], ['TEXT' => "<b>Zonnebloemklas</b>\nVoor leerlingen 3e tot 6e leerjaar die naast het gedifferentieerde aanbod meer nood hebben aan uitdaging."],
        ];
        $r4 = [
            ['TEXT' => '<i>i</i>', 'TEXT_ALIGN' => 'R'], ['TEXT' => "<b>Rekentrein</b>\nExtra ondersteuning bij de automatisatie van hoofdrekenen."],
            ['TEXT' => '<i>f</i>', 'TEXT_ALIGN' => 'R'], ['TEXT' => utf8_decode("<b>Hulpmiddelen</b>\nVoor leerlingen die nood hebben aan materiële ondersteuning.")],
        ];
        $r5 = [
            ['TEXT' => '<i>h</i>', 'TEXT_ALIGN' => 'R'], ['TEXT' => "<b>Leestrein</b>\nExtra ondersteuning bij het technisch lezen."],
            ['TEXT' => '<i>d</i>', 'TEXT_ALIGN' => 'R'], ['TEXT' => "<b>Persoonlijke ondersteuning</b>\nVoor leerlingen die nood hebben aan individuele ondersteuning."],
        ];
        $r6 = [
            ['TEXT' => '<i>p</i>', 'TEXT_ALIGN' => 'R'], ['TEXT' => "<b>Rekentijger</b>\nUitdagender huiswerk."],
            ['TEXT' => '<i>m</i>', 'TEXT_ALIGN' => 'R'], ['TEXT' => utf8_decode("<b>Individuele leerlijn</b>\nVoor leerlingen die voor één of meer vakken een eigen traject volgen op maat van hun kunnen.")],
        ];
        $r7 = [
            ['TEXT' => '<i>e</i>', 'TEXT_ALIGN' => 'R'], ['TEXT' => "<b>Filosofiegroep</b>\nVoor leerlingen die willen nadenken over het leven zelf."],
            ['TEXT' => '<i>s</i>', 'TEXT_ALIGN' => 'R'], ['TEXT' => "<b>Bijtjesklas</b>\nVoor leerlingen die nood hebben aan een extra aanbod voor de lees- en rekenvoorwaarden."],
        ];
        $r8 = [
            ['TEXT' => '<i>o</i>', 'TEXT_ALIGN' => 'R'], ['TEXT' => utf8_decode("<b>Schriftelijke evaluatie</b>\nVakken waar dit symbool naast staat werden schriftelijk geëvalueerd. U vindt deze evaluaties verder in dit portfolio.")],
            ['TEXT' => '<i>k</i>', 'TEXT_ALIGN' => 'R'], ['TEXT' => "<b>Mondelinge evaluatie</b>\nDeze evaluaties werden mondeling besproken met de leerlingen."],
        ];
        $tbl4->addRow($r1);
        $tbl4->addRow($r2);
        $tbl4->addRow($r3);
        $tbl4->addRow($r4);
        $tbl4->addRow($r5);
        $tbl4->addRow($r6);
        $tbl4->addRow($r7);
        $tbl4->addRow($r8);
        $tbl4->close();

        $this->pdf->SetAutoPageBreak(false, 45);
    }

    private function makeFirstPage(StudentResult $student)
    {
        /* ***************************************************
         * NEEMT DEEL AAN
         * **************************************************/
        $this->pdf->y -= 10;
        $left = $this->pdf->x;
        $this->orange();
        $this->pdf->SetFont('Roboto', 'b', 20);
        $this->pdf->Cell(0, 14, 'Ik nam deel aan', 0, 1);

        $this->pdf->SetLineWidth(.5);
        $ly = $this->pdf->y - 3;
        call_user_func_array([$this->pdf, 'SetDrawColor'], Colors::ORANGE);
        $this->pdf->Line($left, $ly, $this->pdf->pageWidth() - ($this->pdf->x * 2), $ly);

        $this->makeParticipationTable($student);

        /* ***************************************************
         * TEACHER COMMENT
         * **************************************************/
        $this->pdf->x = $left;

        $this->orange();
        $this->pdf->SetFont('Roboto', 'b', 20);
        $titular = ($student->getTitularGender() == 'F' ? 'juf ' : 'meester ') . $student->getTitularFirstName();
        $this->pdf->Cell(0, 14, 'Dit wil ' . $titular . ' mij vertellen', 0, 1);

        $this->pdf->SetLineWidth(.5);
        $ly = $this->pdf->y - 3;
        call_user_func_array([$this->pdf, 'SetDrawColor'], Colors::ORANGE);
        $this->pdf->Line($left, $ly, $this->pdf->pageWidth() - ($this->pdf->x * 2), $ly);

        $startY = $this->pdf->y;
        $this->pdf->x = $left;

        $cmc = new Multicell($this->pdf);
        $cmc->setStyle('i', 'Roboto', 'i', 9, Colors::str_blue());
        $cmc->setStyle('b', 'Roboto', 'b', 9, Colors::str_blue());
        $cmc->setStyle('p', 'Roboto', '', 9, Colors::str_blue());

        $this->blue();
        $this->pdf->SetFont('Roboto', '', 9);

        $fb = $student->getFeedback();

        $fb = $this->sanitize($fb);

        $cmc->multiCell($this->pdf->pageWidth() - (2 * $this->leftMargin), 4.5, $fb);

        $endY = $this->pdf->y;

        /*$diffY = $endY - $startY;
        if ($diffY > 60) {
            $this->pdf->y += 60 - $diffY;
        }*/

        /* ***************************************************
         * PARENT COMMENT
         * **************************************************/
        $this->pdf->x = $left;
        $this->orange();
        $this->pdf->SetFont('Roboto', 'b', 20);
        $this->pdf->Cell(0, 14, 'Dit vinden mijn ouders van mijn evaluatie', 0, 1);

        $this->pdf->SetLineWidth(.5);
        $ly = $this->pdf->y - 3;
        call_user_func_array([$this->pdf, 'SetDrawColor'], Colors::ORANGE);
        $this->pdf->Line($left, $ly, $this->pdf->pageWidth() - ($this->pdf->x * 2), $ly);

        $this->pdf->Ln(30);

        /* ***************************************************
         * SELF EVALUATION
         * **************************************************/

        $this->pdf->SetXY($left, -90);
        $this->orange();
        $this->pdf->SetFont('Roboto', 'b', 20);
        $this->pdf->Cell(0, 14, 'Dit vind ik van mijn evaluatie', 0, 1);

        $this->pdf->SetLineWidth(.5);
        $ly = $this->pdf->y - 3;
        call_user_func_array([$this->pdf, 'SetDrawColor'], Colors::ORANGE);
        $this->pdf->Line($left, $ly, $this->pdf->pageWidth() - ($this->pdf->x * 2), $ly);

        $this->pdf->Ln(30);
    }

    private function makeParticipationTable(StudentResult $student)
    {
        $parts = ['GROUP', Redicodi::BASIC, Redicodi::CHALLENGE, Redicodi::BUTTERFLY, Redicodi::MINISUNFLOWER, Redicodi::MATHTRAIN, Redicodi::READTRAIN,
            Redicodi::SUNFLOWER, Redicodi::IAC, Redicodi::TIGER, Redicodi::PHILOSOPHY, Redicodi::BEE];

        $partsCount = count($parts);
        $width = $this->pdf->pageWidth() - ($this->leftMargin * 2);
        $halfPartsCount = ceil($partsCount / 2);

        $colWidth = $width / $halfPartsCount;
        $table = new PdfTable($this->pdf);

        $tblColumns = array_fill(0, $halfPartsCount, $colWidth);
        $table->initialize($tblColumns);
        $table->setStyle('i', 'NotosIcon', '', 24, Colors::str_blue(), .84);
        $table->setStyle('si', 'NotosIcon', '', 16, Colors::str_blue(), .84);

        $table->setRowConfig(['PADDING_TOP' => 4, 'PADDING_BOTTOM' => 4, 'BORDER_TYPE' => 0, 'TEXT_ALIGN' => 'C']);

        $i = 0;
        $row = [];

        foreach ($parts as $part) {
            if ($part == 'GROUP') {
                $row[$i] = [
                    'TYPE' => 'IMAGE',
                    'FILE' => resource_path('icons/groups/' . $student->getGroup() . '.png'),
                    'WIDTH' => 10,
                ];
            } else {
                $icon = '<i>' . Redicodi::icon($part) . '</i>';
                if (in_array($part, $student->getRedicodi())) {
                    $icon .= '  <si>r</si>';
                } else {
                    $icon .= '  <si>q</si>';
                }
                $row[$i] = ['TEXT' => $icon];
            }


            $i++;
            if ($i == $halfPartsCount || $i == $partsCount) {
                $partsCount = $partsCount - $i;
                $i = 0;
                $table->addRow($row);
                $row = [];
            }
        }
        $table->close();
    }

    private function makeSignature()
    {
        $this->pdf->SetY(-70);
        $this->orange();
        $this->pdf->SetFont('Roboto', '', 10);
        $width = ($this->pdf->pageWidth() - ($this->leftMargin * 2)) / 4;
        $i = 0;
        foreach (['leerkracht', 'directeur', 'ouders', 'leerling'] as $item) {
            $this->pdf->SetXY($this->leftMargin + (($width + 2) * $i++), -70);
            $this->pdf->drawTextBox("Handtekening {$item}", $width, 30, 'C');
        }
    }


    private
    function blue()
    {
        call_user_func_array([$this->pdf, 'SetTextColor'], Colors::BLUE);
    }

    private
    function orange()
    {
        call_user_func_array([$this->pdf, 'SetTextColor'], Colors::ORANGE);
    }

    /**
     * @param $fb
     * @return array|mixed|string
     */
    private function sanitize($fb)
    {
        //FIRST html replacements
        $searchHtmlEntities = [
            "<br />",
            "<br/>",
            "</p><p>",
            "&nbsp;",
        ];
        $htmlReplacements = [
            "\n",
            "\n",
            "</p>\n\n<p>",
            " ",
        ];
        $fb = str_replace($searchHtmlEntities, $htmlReplacements, $fb);

        //THEN DECODE POSSIBLE &eacute ... entities
        $fb = html_entity_decode($fb);

        //CLEAN UP ALL DIACRITICS FROM WORD
        //@todo: place this when saving new feedback
        //@todo: is replacement of word diacritics still needed?
        $search = [          // www.fileformat.info/info/unicode/<NUM>/ <NUM> = 2018
            "\xC2\xAB",     // « (U+00AB) in UTF-8
            "\xC2\xBB",     // » (U+00BB) in UTF-8
            "\xE2\x80\x98", // ‘ (U+2018) in UTF-8
            "\xE2\x80\x99", // ’ (U+2019) in UTF-8
            "\xE2\x80\x9A", // ‚ (U+201A) in UTF-8
            "\xE2\x80\x9B", // ‛ (U+201B) in UTF-8
            "\xE2\x80\x9C", // “ (U+201C) in UTF-8
            "\xE2\x80\x9D", // ” (U+201D) in UTF-8
            "\xE2\x80\x9E", // „ (U+201E) in UTF-8
            "\xE2\x80\x9F", // ‟ (U+201F) in UTF-8
            "\xE2\x80\xB9", // ‹ (U+2039) in UTF-8
            "\xE2\x80\xBA", // › (U+203A) in UTF-8
            "\xE2\x80\x93", // – (U+2013) in UTF-8
            "\xE2\x80\x94", // — (U+2014) in UTF-8
            "\xE2\x80\xA6",  // … (U+2026) in UTF-8
        ];

        $replacements = [
            "<<",
            ">>",
            "'",
            "'",
            "'",
            "'",
            '"',
            '"',
            '"',
            '"',
            "<",
            ">",
            "-",
            "-",
            "...",
        ];


        $fb = str_replace($search, $replacements, $fb);

        //cleanups trim spaces at start of line
        $fb = str_replace("\n ", "\n", $fb);

        //utf8 enoding
        $fb = Encoding::toISO8859($fb);
        //$fb = utf8_decode($fb);

        return $fb;
    }


}