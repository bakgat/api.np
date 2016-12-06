<?php
/**
 * Created by PhpStorm.
 * User: karlvaniseghem
 * Date: 25/11/16
 * Time: 15:01
 */
use App\Domain\Services\Pdf\Colors;

/**
 * Default configuration values for the PDF Advanced table
 */

$aDefaultConfiguration = array(

    'TABLE' => array(
        'TABLE_ALIGN' => 'L', //table align on page
        'TABLE_LEFT_MARGIN' => 10, //space to the left margin
        'BORDER_COLOR' => array(0, 92, 177), //border color
        'BORDER_SIZE' => '0.3', //border size
        'BORDER_TYPE' => '0', //border type, can be: 0, 1
    ),

    'HEADER' => array(
        'TEXT_COLOR' => [0, 87, 157], //text color
        'TEXT_SIZE' => 12, //font size
        'TEXT_FONT' => 'Roboto', //font family
        'TEXT_ALIGN' => 'C', //horizontal alignment, possible values: LRCJ (left, right, center, justified)
        'VERTICAL_ALIGN' => 'M', //vertical alignment, possible values: TMB(top, middle, bottom)
        'TEXT_TYPE' => '', //font type
        'LINE_SIZE' => 4, //line size for one row
        'BACKGROUND_COLOR' => false, //background color
        'BORDER_COLOR' => [0, 87, 157], //border color
        'BORDER_SIZE' => 0.5, //border size
        'BORDER_TYPE' => 'B', //border type, can be: 0, 1 or a combination of: "LRTB"
        'TEXT' => ' ', //default text
        //padding
        'PADDING_TOP' => 2, //padding top
        'PADDING_RIGHT' => 1, //padding right
        'PADDING_LEFT' => 1, //padding left
        'PADDING_BOTTOM' => 3, //padding bottom
    ),

    'ROW' => array(
        'TEXT_COLOR' => Colors::BLUE, //text color
        'TEXT_SIZE' => 10, //font size
        'TEXT_FONT' => 'Roboto', //font family
        'TEXT_ALIGN' => 'L', //horizontal alignment, possible values: LRCJ (left, right, center, justified)
        'VERTICAL_ALIGN' => 'M', //vertical alignment, possible values: TMB(top, middle, bottom)
        'TEXT_TYPE' => '', //font type
        'LINE_SIZE' => 4, //line size for one row
        'BACKGROUND_COLOR' => false, //background color
        'BORDER_COLOR' => \App\Domain\Services\Pdf\Colors::llblue(), //border color
        'BORDER_SIZE' => 0.1, //border size
        'BORDER_TYPE' => 'B', //border type, can be: 0, 1 or a combination of: "LRTB"
        'TEXT' => ' ', //default text
        //padding
        'PADDING_TOP' => 3,
        'PADDING_RIGHT' => 1,
        'PADDING_LEFT' => 1,
        'PADDING_BOTTOM' => 3,
    ),
);
