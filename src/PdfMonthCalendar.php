<?php
/**
 * Generate a 2x A5 landscape PDF month calendar
 *
 * PHP version 8
 *
 * @category Calendar
 * @package  MonthCalendar
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
namespace FWieP;
use \IntlDateFormatter as IDF;
use \Mpdf\Output\Destination as D;

/**
 * Generate a 2x A5 landscape PDF month calendar
 *
 * PHP version 8
 *
 * @category Calendar
 * @package  MonthCalendar
 * @author   Frans-Willem Post (FWieP) <fwiep@fwiep.nl>
 * @license  https://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.fwiep.nl/
 */
class PdfMonthCalendar
{
    /**
     * This calendar's year
     *
     * @var integer
     */
    private $_year = 0;

    /**
     * This calendar's locale
     * 
     * @var string
     */
    private $_locale = 'nl_NL';

    /**
     * This calendar's timezone
     * 
     * @var \DateTimeZone
     */
    private $_tz;

    /**
     * Wraps the given DateTime and adds/subtracts given amount of days
     *
     * @param \DateTime $dt   DateTime to wrap
     * @param int       $days amount of days to add or subtract, can be negative
     *
     * @return \DateTime
     */
    private static function _dtWrap(\DateTime $dt, int $days) : \DateTime
    {
        $outDt = clone $dt;
        $di = new \DateInterval('P'.abs($days).'D');

        if ($days < 0) {
            $outDt->sub($di);
        } else {
            $outDt->add($di);
        }
        return $outDt;
    }

    /**
     * Creates a new month calendar
     *
     * @param int $year the year to display
     */
    public function __construct(int $year)
    {
        if ($year < 1582 || $year > 3000) {
            throw new \InvalidArgumentException(
                "Year should be between 1582 and 3000!"
            );
        }
        $this->_year = $year;
        $this->_tz = new \DateTimeZone('Europe/Amsterdam');
    }

    /**
     * Generates an HTML-table of the given month
     * 
     * @param int $m the month
     * 
     * @return string the output HTML
     */
    private function _generateMonthTable(int $m) : string
    {
        $firstThisMonth = new \DateTime($this->_year.'-'.$m.'-01');
        $loopDate = clone $firstThisMonth;
        
        $dtfmt = new IDF(
            $this->_locale, IDF::NONE, IDF::NONE, $this->_tz, IDF::GREGORIAN
        );
        $html = '<table class="month">';

        $dtfmt->setPattern('MMMM');
        $html .= sprintf(
            '<tr class="title"><th colspan="7">%s %d</th></tr>',
            $dtfmt->format($firstThisMonth),
            $this->_year
        );
        while ($loopDate->format('N') > 1) {
            $loopDate->sub(new \DateInterval('P1D'));
        }
        // Array of 8 rows for weeknumbers (1) + weekdays (7)
        $rows = array_fill(-1, 8, []);

        // Construct first row (for weeknumbers)
        $dtfmt->setPattern('ww');
        for ($weekLoop = -1; $weekLoop < 6; $weekLoop++) {
            if ($weekLoop == -1) {
                $rows[-1][] = '<th>wk</th>';
                continue;
            }
            $dt = self::_dtWrap($firstThisMonth, 7*$weekLoop);
            $rows[-1][] = sprintf('<td>%s</td>', $dtfmt->format($dt));
        }
        // Construct second to eighth rows
        foreach ($rows as $rowIx => &$row) {
            if ($rowIx == -1) {
                continue;
            }
            for ($colIx = -1; $colIx < 6; $colIx++) {
                $dt = self::_dtWrap($loopDate, 7*$colIx + $rowIx);

                if ($colIx == -1) {
                    // Print abbreviated weekday name
                    $dtfmt->setPattern('E');
                    $row[] = sprintf('<th>%s</th>', $dtfmt->format($dt));
                    continue;
                }
                if ($dt->format('m') != $m) {
                    // Print empty cell
                    $row[] = '<td>&nbsp;</td>';
                    continue;
                }
                // Print day of month
                $dtfmt->setPattern('d');
                $row[] = sprintf('<td>%s</td>', $dtfmt->format($dt));
            }
        }
        foreach ($rows as $rowIx => &$row) {
            if ($rowIx == -1) {
                $html .= '<tr class="week">';
            } else {
                $html .= '<tr>';
            }
            $html .= implode('', $row);
            $html .= '</tr>';
        }
        $html .= '</table>';
        return $html;
    }

    /**
     * Generates a 2x A5 landscape year calendar PDF and outputs it to the browser
     *
     * @return void
     */
    public function getPDF() : void
    {
        $pdfConfig = array(
            'format' => 'A5',
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 5,
            'margin_bottom' => 5,
            'margin_header' => 0,
            'margin_footer' => 0,
            'orientation' => 'L',
        );
        $pdf = new \Mpdf\Mpdf($pdfConfig);
        $pdf->SetTitle('Maandkalender '.$this->_year);
        $pdf->SetAuthor('Frans-Willem Post (FWieP)');
        
        $css = file_get_contents('style.css');
        $pdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);

        $pdf->AddPage();
        $html = '<table class="scaffold"><tr>';
        for ($m = 1; $m <= 3; $m++) {
            // Add january - march to the first page's first row
            $html .= '<td>'.$this->_generateMonthTable($m).'</td>';
        }
        $html .= '</tr><tr>';
        for ($m = 4; $m <= 6; $m++) {
            // Add april - june to the first page's second row
            $html .= '<td>'.$this->_generateMonthTable($m).'</td>';
        }
        $html .= '</tr></table>';
        $pdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

        $pdf->AddPage();
        $html = '<table class="scaffold"><tr>';
        for ($m = 7; $m <= 9; $m++) {
            // Add july - september to the second page's first row
            $html .= '<td>'.$this->_generateMonthTable($m).'</td>';
        }
        $html .= '</tr><tr>';
        for ($m = 10; $m <= 12; $m++) {
            // Add october - december to the second page's second row
            $html .= '<td>'.$this->_generateMonthTable($m).'</td>';
        }
        $html .= '</tr></table>';
        $pdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

        $pdf->Output('Maandkalender '.$this->_year.'.pdf', D::DOWNLOAD);
    }
}